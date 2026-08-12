import json

log_file = r'C:\Users\ppara\.gemini\antigravity\brain\1696a386-14b5-4d24-974b-dd8ee166ea99\.system_generated\logs\transcript.jsonl'

with open(log_file, 'r', encoding='utf-8') as f:
    for line in f:
        if 'footer.php' in line:
            data = json.loads(line)
            calls = data.get('tool_calls', [])
            for c in calls:
                args = c.get('args', {})
                if 'footer.php' in str(args.get('TargetFile', '')) or 'footer.php' in str(args.get('AbsolutePath', '')):
                    print(f"STEP {data.get('step_index')} [{c.get('name')}]:")
                    if 'TargetContent' in args:
                        print("--- TARGET ---")
                        print(args['TargetContent'])
                    if 'ReplacementContent' in args:
                        print("--- REPLACEMENT ---")
                        print(args['ReplacementContent'])
                    if 'CodeContent' in args:
                        print("--- CODE ---")
                        print(args['CodeContent'])
