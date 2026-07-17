#!/usr/bin/env python3
from pathlib import Path
import re, subprocess, sys
ROOT=Path(__file__).resolve().parents[1]
errors=[]; notes=[]

# PHP lint
for p in ROOT.rglob('*.php'):
    r=subprocess.run(['php','-l',str(p)],capture_output=True,text=True)
    if r.returncode: errors.append(f'PHP syntax: {p.relative_to(ROOT)}: {r.stdout}{r.stderr}')

# JS syntax
for p in (ROOT/'assets/js').glob('*.js'):
    r=subprocess.run(['node','--check',str(p)],capture_output=True,text=True)
    if r.returncode: errors.append(f'JS syntax: {p.relative_to(ROOT)}: {r.stderr}')

# NUL bytes
for p in ROOT.rglob('*'):
    if p.is_file() and '.git' not in p.parts and p.suffix.lower() in {'.php','.js','.css','.html','.md','.sql','.json'}:
        try: b=p.read_bytes()
        except OSError: continue
        if b'\x00' in b: errors.append(f'NUL byte: {p.relative_to(ROOT)}')

# Required files
required=[
 'index.php','config/database.php','helpers/security.php','controllers/authController.php',
 'assets/js/ajax-app.js','views/layouts/sidebar.php','views/manager/_layout.php',
 'views/manager/pending_approval.php','project_management.sql'
]
for rel in required:
    if not (ROOT/rel).exists(): errors.append(f'Missing required file: {rel}')

# Feature markers
checks={
 'CSRF meta/token': ['csrf_meta_tag()', 'require_csrf_token'],
 'RBAC backend': ['require_action_role', 'ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_STAFF'],
 'JWT': ['apiLogin','apiRefreshToken','apiMe','API_SESSION','jwt_decode_token'],
 'Password hashing': ['password_hash(', 'password_verify('],
 'Session regeneration': ['session_regenerate_id(true)'],
 'Prepared statements': ['->prepare('],
 'XSS escaping': ['htmlspecialchars(', 'app_escape('],
}
alltext='\n'.join(p.read_text(errors='ignore') for p in ROOT.rglob('*.php'))
for name,markers in checks.items():
    missing=[m for m in markers if m not in alltext]
    if missing: errors.append(f'{name}: missing markers {missing}')
    else: notes.append(f'PASS {name}')

# Manager sidebar consistency
layout=(ROOT/'views/manager/_layout.php').read_text(errors='ignore')
dash=(ROOT/'views/dashboard/manager.php').read_text(errors='ignore')
for marker in ['Chờ phê duyệt','manager_sidebar_counts','pending_approvals']:
    if marker not in layout+dash: errors.append(f'Manager sidebar missing: {marker}')
if "manager_sidebar($manager, 'overview'" not in dash:
    errors.append('Manager dashboard does not render shared manager_sidebar()')

# Admin sidebar consistency
admin=(ROOT/'views/layouts/sidebar.php').read_text(errors='ignore')
for marker in ['ProjectHub','Admin workspace','Tổng quan','Quản lý người dùng','Phân quyền','Dự án','Nhiệm vụ','Thông báo']:
    if marker not in admin: errors.append(f'Admin sidebar missing: {marker}')
if 'Need Help?' in admin: errors.append('Admin sidebar still contains Need Help')

print('\n'.join(notes))
if errors:
    print('\nFAILURES:')
    print('\n'.join('- '+e for e in errors))
    sys.exit(1)
print('\nSTATIC QA PASSED')
