#!/bin/bash

file="resources/views/layouts/app.blade.php"

echo "Tracing @if/@endif blocks in $file:"
echo "===================================="

grep -n "@if\|@endif\|@else" "$file"

echo -e "\n\nValidating nesting (should show balanced closing):"

# Simple state machine
depth=0
while IFS=: read -r line_num line_content; do
    if echo "$line_content" | grep -q "@if"; then
        depth=$((depth+1))
        echo "$line_num: [Depth $depth] OPEN: $line_content"
    elif echo "$line_content" | grep -q "@endif"; then
        echo "$line_num: [Depth $depth] CLOSE: $line_content"
        depth=$((depth-1))
    elif echo "$line_content" | grep -q "@else\|@elseif"; then
        echo "$line_num: [Depth $depth] MIDDLE: $line_content"
    fi
done < <(grep -n "@if\|@endif\|@else\|@elseif" "$file")

echo -e "\nFinal depth: $depth (should be 0)"

