#!/bin/bash

# WordPress Performance Monitor
# Zeigt kontinuierlich Performance-Metriken an

watch -n 2 'echo "=== WordPress Performance Monitor ===" && \
echo "" && \
echo "Container Resources:" && \
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" | head -7 && \
echo "" && \
echo "Response Times:" && \
curl -w "Time: %{time_total}s\n" -s -o /dev/null https://www.badspiegel.local || echo "Site nicht erreichbar" && \
echo "" && \
echo "Last updated: $(date)"'
