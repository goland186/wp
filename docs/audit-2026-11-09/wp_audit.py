#!/usr/bin/env python3
"""Полный аудит старой базы WordPress (SQLite-копия дампа)."""
import json
import re
import sqlite3

DB = "/home/z/my-project/wp-migration/wp_old.sqlite"
OUT = "/home/z/my-project/wp-migration/audit_db.json"

con = sqlite3.connect(DB)
con.row_factory = sqlite3.Row
cur = con.cursor()
R = {}


def opt(name):
    row = cur.execute("SELECT option_value FROM wpla_options WHERE option_name=?", (name,)).fetchone()
    return row[0] if row else None


R["siteurl"] = opt("siteurl")
R["home"] = opt("home")
R["blogname"] = opt("blogname")
R["blogdescription"] = opt("blogdescription")
R["admin_email"] = opt("admin_email")
R["db_version"] = opt("db_version")
R["template"] = opt("template")
R["stylesheet"] = opt("stylesheet")
R["permalink_structure"] = opt("permalink_structure")
R["wplang"] = opt("WPLANG")
R["timezone"] = opt("timezone_string")
R["users_can_register"] = opt("users_can_register")
R["comment_moderation"] = opt("comment_moderation")

# активные плагины (сериализованный массив)
ap = opt("active_plugins") or ""
R["active_plugins_raw"] = re.findall(r"s:\d+:\"([^\"]+\.php)\"", ap)

# пользователи
R["users"] = cur.execute("SELECT count(*) c FROM wpla_users").fetchone()["c"]
R["user_logins"] = [r["user_login"] for r in cur.execute("SELECT user_login FROM wpla_users LIMIT 20")]

# типы записей
R["post_types"] = [
    dict(r) for r in cur.execute(
        "SELECT post_type, COUNT(*) n, MIN(post_date) first, MAX(post_date) last "
        "FROM wpla_posts WHERE post_status NOT IN ('auto-draft','trash') "
        "GROUP BY post_type ORDER BY n DESC")]

# статусы
R["post_statuses"] = [dict(r) for r in cur.execute(
    "SELECT post_status, COUNT(*) n FROM wpla_posts GROUP BY post_status ORDER BY n DESC")]

# страницы
R["pages"] = [dict(r) for r in cur.execute(
    "SELECT ID, post_title, post_name, post_status, post_author, post_modified "
    "FROM wpla_posts WHERE post_type='page' AND post_status NOT IN ('trash','auto-draft') "
    "ORDER BY menu_order, ID")]

# записи (посты)
R["posts_count_published"] = cur.execute(
    "SELECT count(*) c FROM wpla_posts WHERE post_type='post' AND post_status='publish'").fetchone()["c"]

# вложения-картинки
R["attachments"] = cur.execute(
    "SELECT count(*) c FROM wpla_posts WHERE post_type='attachment'").fetchone()["c"]
R["attachment_mime"] = [dict(r) for r in cur.execute(
    "SELECT post_mime_type m, COUNT(*) n FROM wpla_posts WHERE post_type='attachment' GROUP BY 1 ORDER BY n DESC LIMIT 10")]

# таксономии
R["taxonomies"] = [dict(r) for r in cur.execute(
    "SELECT taxonomy, COUNT(*) n FROM wpla_term_taxonomy GROUP BY taxonomy ORDER BY n DESC")]

# меню
R["menus"] = [dict(r) for r in cur.execute(
    "SELECT t.term_id, t.name, tt.count FROM wpla_terms t JOIN wpla_term_taxonomy tt ON t.term_id=tt.term_id WHERE tt.taxonomy='nav_menu'")]

# виджеты (sidebars_widgets)
sw = opt("sidebars_widgets") or ""
R["sidebars_widgets_raw_head"] = sw[:2000]

# конструкторы страниц
def like_count(pattern):
    return cur.execute("SELECT count(*) c FROM wpla_posts WHERE post_content LIKE ?", (f"%{pattern}%",)).fetchone()["c"]

R["builders"] = {
    "gutenberg (wp-block-)": like_count("wp-block-"),
    "wpbakery ([vc_row]": like_count("[vc_row"),
    "elementor (в контенте)": like_count("data-elementor-type"),
    "elementor (мета _elementor_data)": cur.execute("SELECT count(*) c FROM wpla_postmeta WHERE meta_key='_elementor_data'").fetchone()["c"],
    "divi (et_pb_)": like_count("et_pb_"),
    "shortcodes общее": like_count("["),
}

# ACF
R["acf_field_groups"] = cur.execute(
    "SELECT count(*) c FROM wpla_posts WHERE post_type='acf-field-group'").fetchone()["c"]
R["acf_fields"] = cur.execute(
    "SELECT count(*) c FROM wpla_posts WHERE post_type='acf-field'").fetchone()["c"]
R["acf_keys_in_meta"] = cur.execute(
    "SELECT count(DISTINCT meta_key) c FROM wpla_postmeta WHERE meta_key NOT LIKE '\\_%' AND meta_key LIKE '%\\_%'").fetchone()["c"]

# топ meta-ключей
R["top_meta_keys"] = [dict(r) for r in cur.execute(
    "SELECT meta_key, COUNT(*) n FROM wpla_postmeta GROUP BY meta_key ORDER BY n DESC LIMIT 40")]

# yoast
R["yoast_title"] = cur.execute("SELECT count(*) c FROM wpla_postmeta WHERE meta_key='_yoast_wpseo_title'").fetchone()["c"]
R["yoast_desc"] = cur.execute("SELECT count(*) c FROM wpla_postmeta WHERE meta_key='_yoast_wpseo_metadesc'").fetchone()["c"]

# доп. таблицы плагинов
R["plugin_tables_counts"] = {}
for t in ["wpla_sam_ads", "wpla_sam_places", "wpla_gallery_galleries", "wpla_gallery_slides",
          "wpla_g_routeplanner_plans", "wpla_supsystic_ss_projects", "wpla_ulogin",
          "wpla_wslusersprofiles", "wpla_duplicator_packages", "wpla_comments"]:
    try:
        R["plugin_tables_counts"][t] = cur.execute(f"SELECT count(*) c FROM {t}").fetchone()["c"]
    except Exception as e:
        R["plugin_tables_counts"][t] = f"err: {e}"

# версии/признаки WP из транзиентов и опций
dupe = opt("duplicator_package_active") or ""
m = re.search(r'VersionWP";s:\d+:"([\d.]+)"', dupe)
R["wp_version_from_duplicator"] = m.group(1) if m else None
m = re.search(r'VersionDB";s:\d+:"([\d.]+)"', dupe)
R["mysql_from_duplicator"] = m.group(1) if m else None
m = re.search(r'VersionPHP";s:\d+:"([\d.]+)"', dupe)
R["php_from_duplicator"] = m.group(1) if m else None

# примеры контента (первые 3 страницы)
R["content_samples"] = []
for r in cur.execute("SELECT ID, post_title, substr(post_content,1,400) c FROM wpla_posts WHERE post_type='page' AND post_status='publish' LIMIT 3"):
    R["content_samples"].append(dict(r))

json.dump(R, open(OUT, "w", encoding="utf-8"), ensure_ascii=False, indent=2, default=str)

# краткая сводка
print("=== ОСНОВНОЕ ===")
for k in ["siteurl", "blogname", "db_version", "template", "stylesheet", "permalink_structure",
          "wplang", "wp_version_from_duplicator", "php_from_duplicator", "users"]:
    print(f"{k}: {R[k]}")
print("\n=== АКТИВНЫЕ ПЛАГИНЫ ===")
for p in R["active_plugins_raw"]:
    print(" -", p)
print("\n=== ТИПЫ ЗАПИСЕЙ ===")
for pt in R["post_types"]:
    print(f" {pt['post_type']}: {pt['n']} ({pt['first']}..{pt['last']})")
print("\n=== КОНСТРУКТОРЫ ===")
for k, v in R["builders"].items():
    print(f" {k}: {v}")
print("\n=== МЕНЮ ===")
for m_ in R["menus"]:
    print(f" #{m_['term_id']} {m_['name']} ({m_['count']} эл.)")
print(f"\nСтраниц всего: {len(R['pages'])}, вложений: {R['attachments']}, Yoast title/desc: {R['yoast_title']}/{R['yoast_desc']}")
print("ACF: групп", R["acf_field_groups"], "полей", R["acf_fields"])
print("Детали в", OUT)
