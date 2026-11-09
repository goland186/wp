#!/usr/bin/env python3
"""Надёжный конвертер MySQL-дампа WordPress -> SQLite.
Парсит посимвольно с учётом строк/экранирования, поэтому многострочные
данные (виджеты, счётчики) не ломают импорт."""
import re
import sqlite3
import sys

SRC = "/home/z/my-project/wp-migration/repo/database/old_dump.sql"
DST = "/home/z/my-project/wp-migration/wp_old.sqlite"

TYPE_MAP = [
    (r"(tinyint|smallint|mediumint|bigint|int)\b", "INTEGER"),
    (r"(decimal|float|double)\b", "REAL"),
    (r"(varchar|char|text|tinytext|mediumtext|longtext)\b", "TEXT"),
    (r"(datetime|timestamp|date|time)\b", "TEXT"),
    (r"(blob|tinyblob|mediumblob|longblob|binary|varbinary|bit)\b", "BLOB"),
    (r"enum\s*\([^)]*\)", "TEXT"),
    (r"(json|geometry|point)\b", "TEXT"),
]


def map_type(t):
    for pat, rep in TYPE_MAP:
        if re.search(pat, t, re.I):
            return rep
    return "TEXT"


def tokenize_values(s):
    """Разбирает '(...) , (...)' MySQL VALUES -> список кортежей python."""
    rows, cur_row, buf = [], [], []
    depth = 0
    i = 0
    in_str = False

    def close_field():
        cur_row.append("".join(buf))
        buf.clear()

    while i < len(s):
        c = s[i]
        if in_str:
            if c == "\\":
                nxt = s[i + 1]
                mp = {"n": "\n", "r": "\r", "0": "\0", "Z": "\x1a", "b": "\b",
                      "t": "\t", "'": "'", '"': '"', "\\": "\\"}
                buf.append(mp.get(nxt, nxt))
                i += 2
                continue
            if c == "'":
                in_str = False
            buf.append(c)
            i += 1
            continue
        if c == "'":
            in_str = True
            buf.append(c)
            i += 1
            continue
        if c == "(":
            depth += 1
            if depth == 1:
                buf.clear()
                cur_row = []
                i += 1
                continue
        if c == ")":
            depth -= 1
            if depth == 0:
                close_field()
                rows.append(cur_row)
                cur_row = []
                i += 1
                continue
        if c == "," and depth == 1:
            close_field()
            i += 1
            continue
        buf.append(c)
        i += 1
    out = []
    for r in rows:
        vals = []
        for raw in r:
            raw = raw.strip()
            if raw.upper() == "NULL":
                vals.append(None)
            elif raw.startswith("0x"):
                try:
                    vals.append(bytes.fromhex(raw[2:]))
                except ValueError:
                    vals.append(raw)
            elif raw.startswith("'"):
                vals.append(raw[1:-1] if raw.endswith("'") else raw[1:])
            elif re.fullmatch(r"-?\d+", raw):
                vals.append(int(raw))
            elif re.fullmatch(r"-?\d*\.\d+(e-?\d+)?", raw, re.I):
                vals.append(float(raw))
            else:
                vals.append(raw)  # функции MySQL редки в дампах WP
        out.append(vals)
    return out


def main():
    text = open(SRC, encoding="utf-8", errors="replace").read()
    con = sqlite3.connect(DST)
    cur = con.cursor()
    n_tables = n_ins = skipped = 0
    errors = open("/home/z/my-project/wp-migration/import_errors.log", "w", encoding="utf-8")

    # разрезаем на стейтменты с учётом кавычек
    stmts, buf, in_str, i = [], [], False, 0
    while i < len(text):
        c = text[i]
        if in_str:
            buf.append(c)
            if c == "\\":
                buf.append(text[i + 1])
                i += 2
                continue
            if c == "'":
                in_str = False
        elif c == "'":
            in_str = True
            buf.append(c)
        elif text.startswith("/*!", i):
            j = text.find("*/", i)
            i = (j + 2) if j != -1 else len(text)
            continue
        elif text.startswith("-- ", i) or text.startswith("--\n", i):
            j = text.find("\n", i)
            i = j if j != -1 else len(text)
            continue
        elif c == ";" and not in_str:
            stmts.append("".join(buf))
            buf = []
        else:
            buf.append(c)
        i += 1
    if buf:
        stmts.append("".join(buf))

    for st in stmts:
        st = st.strip()
        if not st:
            continue
        up = st.lstrip().upper()
        if up.startswith(("SET ", "LOCK TABLES", "UNLOCK TABLES", "DROP TABLE", "CREATE DATABASE", "USE ")):
            skipped += 1
            continue
        m = re.match(r"CREATE TABLE `?(\w+)`?\s*\((.*)\)\s*(ENGINE|DEFAULT|AUTO_INCREMENT|COMMENT|CHARSET|$)", st, re.S | re.I)
        if m:
            table, body = m.group(1), m.group(2)
            cols, indexes = [], []
            depth = 0
            parts, cur_b = [], []
            for ch in body:
                if ch == "(":
                    depth += 1
                if ch == ")":
                    depth -= 1
                if ch == "," and depth == 0:
                    parts.append("".join(cur_b))
                    cur_b = []
                else:
                    cur_b.append(ch)
            if cur_b:
                parts.append("".join(cur_b))
            pk = []
            for p in parts:
                p = p.strip()
                if not p:
                    continue
                pm = re.match(r"`(\w+)`\s+(.+)", p, re.S)
                if pm and not re.match(r"(PRIMARY|unique|key|fulltext|constraint|index|foreign)\b", pm.group(2), re.I):
                    name, defn = pm.group(1), pm.group(2)
                    typ = map_type(defn)
                    line = f'"{name}" {typ}'
                    if re.search(r"\bAUTO_INCREMENT\b", defn, re.I):
                        pk = [name]
                    if re.search(r"\bNOT NULL\b", defn, re.I):
                        line += " NOT NULL"
                    dm = re.search(r"DEFAULT\s+(NULL|'[^']*'|[-\d.]+|CURRENT_TIMESTAMP)", defn, re.I)
                    if dm and not re.search(r"\bAUTO_INCREMENT\b", defn, re.I):
                        line += f" DEFAULT {dm.group(1)}"
                    cols.append(line)
                elif re.match(r"PRIMARY KEY", p, re.I):
                    cols.append("PRIMARY KEY (" + re.sub(r"PRIMARY KEY \(|GET_FORMAT.*", "", p, flags=re.I).replace("`", '"') + ")")
                elif re.match(r"(UNIQUE )?KEY|INDEX", p, re.I):
                    km = re.match(r"(?:UNIQUE |FULLTEXT |SPATIAL )?KEY `?(\w+)`?\s*\((.*)\)", p, re.I | re.S)
                    if km:
                        idx_cols = re.sub(r"\(\d+\)", "", km.group(2)).replace("`", '"')
                        u = "UNIQUE " if p.lower().startswith("unique") else ""
                        indexes.append(f'CREATE {u}INDEX "ix_{table}_{km.group(1)}" ON "{table}" ({idx_cols});')
                    continue
            if pk:
                cols.append("PRIMARY KEY (" + ", ".join(f'"{c}"' for c in pk) + ")")
            try:
                cur.execute(f'CREATE TABLE "{table}" (\n  ' + ",\n  ".join(cols) + "\n)")
                n_tables += 1
                for ix in indexes:
                    try:
                        cur.execute(ix)
                    except sqlite3.Error as e:
                        errors.write(f"INDEX {table}: {e}\n")
            except sqlite3.Error as e:
                errors.write(f"CREATE {table}: {e}\n{st[:300]}\n---\n")
            continue
        mi = re.match(r"INSERT INTO `?(\w+)`?\s*(?:\(([^)]+)\))?\s*VALUES\s*(.*)", st, re.S | re.I)
        if mi:
            table, colspec, vals = mi.group(1), mi.group(2), mi.group(3)
            rows = tokenize_values(vals)
            if colspec:
                cols = [c.strip().strip("`") for c in colspec.split(",")]
                q = f'INSERT INTO "{table}" ({",".join(chr(34)+c+chr(34) for c in cols)}) VALUES ({",".join("?"*len(cols))})'
            else:
                ncols = cur.execute(f"PRAGMA table_info('{table}')").fetchall()
                q = f'INSERT INTO "{table}" VALUES ({",".join("?"*len(ncols))})'
            try:
                cur.executemany(q, rows)
                n_ins += len(rows)
            except sqlite3.Error as e:
                errors.write(f"INSERT {table}: {e} rows={len(rows)}\n")
            continue
        skipped += 1
    errors.close()
    con.commit()
    tables = cur.execute("SELECT count(*) FROM sqlite_master WHERE type='table'").fetchone()[0]
    posts = cur.execute("SELECT count(*) FROM wpla_posts").fetchone()[0]
    opts = cur.execute("SELECT count(*) FROM wpla_options").fetchone()[0]
    meta = cur.execute("SELECT count(*) FROM wpla_postmeta").fetchone()[0]
    print(f"Таблиц создано: {tables} | строк INSERT: {n_ins} | прочих стейтментов: {skipped}")
    print(f"wpla_posts: {posts} | wpla_options: {opts} | wpla_postmeta: {meta}")


if __name__ == "__main__":
    main()
