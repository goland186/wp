#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Сверка реальной папки wp-content/uploads со списком ожидаемых файлов
(uploads_paths_expected_full.txt из реестра ссылок).

Использование:
    python3 compare_uploads.py <путь к папке uploads> [путь к списку ожидаемых]

Выход:
    output/uploads_diff_report.json  — сводка
    output/uploads_missing.csv       — файлы, на которые есть ссылки, но файлов нет
    output/uploads_extra.csv         — файлы есть, но ссылок в БД не найдено (ок)
"""
import os, sys, json, csv
from collections import Counter

BASE = '/home/z/my-project/wp-migration'
EXPECTED_DEFAULT = os.path.join(BASE, 'output/uploads_paths_expected_full.txt')
OUT = os.path.join(BASE, 'output')
os.makedirs(OUT, exist_ok=True)

uploads_dir = sys.argv[1] if len(sys.argv) > 1 else None
expected_file = sys.argv[2] if len(sys.argv) > 2 else EXPECTED_DEFAULT

if not uploads_dir or not os.path.isdir(uploads_dir):
    print('Укажите путь к папке uploads: python3 compare_uploads.py /path/to/uploads')
    sys.exit(1)

expected = set()
with open(expected_file, encoding='utf-8') as f:
    for line in f:
        line = line.strip()
        if line:
            expected.add(line)

actual = set()
for root, _dirs, files in os.walk(uploads_dir):
    rel_root = os.path.relpath(root, uploads_dir)
    for fn in files:
        p = fn if rel_root == '.' else f'{rel_root}/{fn}'
        actual.add(p.replace(os.sep, '/'))

missing = sorted(expected - actual)     # есть ссылки, нет файлов
extra = sorted(actual - expected)       # есть файлы, нет ссылок в БД

with open(f'{OUT}/uploads_missing.csv', 'w', newline='', encoding='utf-8-sig') as f:
    w = csv.writer(f); w.writerow(['relative_path'])
    for p in missing: w.writerow([p])

with open(f'{OUT}/uploads_extra.csv', 'w', newline='', encoding='utf-8-sig') as f:
    w = csv.writer(f); w.writerow(['relative_path'])
    for p in extra: w.writerow([p])

missing_by_dir = Counter(p.split('/')[0] + '/' + (p.split('/')[1] if '/' in p else '-') for p in missing if '/' in p)

report = {
    'uploads_dir': os.path.abspath(uploads_dir),
    'expected_files': len(expected),
    'actual_files': len(actual),
    'missing_count': len(missing),
    'extra_count': len(extra),
    'coverage_percent': round(100 * (len(expected) - len(missing)) / max(len(expected), 1), 2),
    'missing_by_year_month': dict(sorted(missing_by_dir.items())),
    'missing_sample': missing[:50],
    'verdict': 'OK: все файлы на месте' if len(missing) == 0
               else f'НЕДОСТАЮЩИХ ФАЙЛОВ: {len(missing)} — см. uploads_missing.csv',
}
with open(f'{OUT}/uploads_diff_report.json', 'w', encoding='utf-8') as f:
    json.dump(report, f, ensure_ascii=False, indent=1)

print(json.dumps({k: v for k, v in report.items() if k != 'missing_sample'}, ensure_ascii=False, indent=1))
if missing:
    print('Примеры недостающих:', missing[:10])
