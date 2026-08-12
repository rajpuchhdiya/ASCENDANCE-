import json

log_file = r'C:\Users\ppara\.gemini\antigravity\brain\1696a386-14b5-4d24-974b-dd8ee166ea99\.system_generated\logs\transcript.jsonl'

with open(log_file, 'r', encoding='utf-8') as f:
    for line in f:
        data = json.loads(line)
        if data.get('step_index') == 995:
            calls = data.get('tool_calls', [])
            for c in calls:
                args = c.get('args', {})
                print(args.get('TargetContent'))
