import json

log_file = r'C:\Users\ppara\.gemini\antigravity\brain\1696a386-14b5-4d24-974b-dd8ee166ea99\.system_generated\logs\transcript.jsonl'
footer_file = r'c:\XAMPP\htdocs\Ascendance\wp-content\themes\ascendance\footer.php'

with open(log_file, 'r', encoding='utf-8') as f:
    for line in f:
        data = json.loads(line)
        if data.get('step_index') == 995:
            calls = data.get('tool_calls', [])
            for c in calls:
                args = c.get('args', {})
                target_content = args.get('TargetContent')
                if target_content:
                    # Parse JSON string if target_content is JSON-encoded
                    try:
                        clean_content = json.loads(target_content)
                    except Exception:
                        clean_content = target_content
                    with open(footer_file, 'w', encoding='utf-8') as fout:
                        fout.write(clean_content)
                    print("Cleanly restored old footer.php!")
