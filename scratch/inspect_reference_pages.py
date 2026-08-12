import os, re

ref_dir = r'c:\XAMPP\htdocs\Ascendance\as\project\ui_kits\reference'
files = [f for f in os.listdir(ref_dir) if f.endswith('.html')]

for f in files:
    filepath = os.path.join(ref_dir, f)
    with open(filepath, 'r', encoding='utf-8') as fname:
        content = fname.read()
    
    has_style = '<style>' in content
    has_script = '<script>' in content
    print(f"File: {f} (Size: {len(content)} bytes) | Style: {has_style} | Script: {has_script}")
