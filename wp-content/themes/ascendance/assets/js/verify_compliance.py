import re
import os

theme_dir = r"c:\XAMPP\htdocs\Ascendance\wp-content\themes\ascendance"
plugin_dir = r"c:\XAMPP\htdocs\Ascendance\wp-content\plugins\ascendance-core"

compliance_passed = True

def check_file(filepath):
    global compliance_passed
    with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()

    basename = os.path.basename(filepath)

    # 1. Banned Purple Accent Check
    if "accent-purple" in content or "accent_purple" in content:
        print(f"FAIL: {filepath} contains legacy purple references.")
        compliance_passed = False

    # 2. Banned Gradient check (only in CSS files)
    if filepath.endswith(".css") and "linear-gradient" in content:
        print(f"FAIL: {filepath} contains linear-gradient styles.")
        compliance_passed = False

    # 3. Hardcoded border-radius values > 2px check
    # Match border-radius: Xpx or border-radius: X% where X > 2
    # Allow border-radius: var(...)
    radius_matches = re.findall(r"border-radius:\s*([0-9\.]+)(px|%)", content)
    for value, unit in radius_matches:
        if unit == "px":
            val = float(value)
            if val > 2.0:
                print(f"FAIL: {filepath} contains hardcoded border-radius of {val}px.")
                compliance_passed = False
        elif unit == "%":
            val = float(value)
            if val > 2.0:  # e.g., 50% is banned because it creates circular/pill elements
                print(f"FAIL: {filepath} contains border-radius of {val}%.")
                compliance_passed = False

# Walk through theme and plugin files
for root, dirs, files in os.walk(theme_dir):
    norm_root = root.replace("\\", "/")
    if "node_modules" in norm_root or "assets/dist" in norm_root or ".git" in norm_root:
        continue
    for file in files:
        if file.endswith((".php", ".css", ".js")):
            check_file(os.path.join(root, file))

for root, dirs, files in os.walk(plugin_dir):
    for file in files:
        if file.endswith((".php", ".css", ".js")):
            check_file(os.path.join(root, file))

if compliance_passed:
    print("SUCCESS: All style compliance checks passed!")
else:
    print("FAILURE: Style compliance checks failed.")
