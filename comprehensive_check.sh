#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

echo "COMPREHENSIVE BLADE DIRECTIVE CHECK"
echo "===================================="

find resources/views -name "*.blade.php" -type f | sort | while read file; do
    has_error=0
    
    # Count all directive pairs
    if_count=$(grep -o '@if' "$file" | wc -l)
    endif_count=$(grep -o '@endif' "$file" | wc -l)
    
    foreach_count=$(grep -o '@foreach' "$file" | wc -l)
    endforeach_count=$(grep -o '@endforeach' "$file" | wc -l)
    
    forelse_count=$(grep -o '@forelse' "$file" | wc -l)
    endforelse_count=$(grep -o '@endforelse' "$file" | wc -l)
    
    section_count=$(grep -o '@section' "$file" | wc -l)
    endsection_count=$(grep -o '@endsection' "$file" | wc -l)
    
    push_count=$(grep -o '@push' "$file" | wc -l)
    endpush_count=$(grep -o '@endpush' "$file" | wc -l)
    
    php_count=$(grep -o '@php' "$file" | wc -l)
    endphp_count=$(grep -o '@endphp' "$file" | wc -l)
    
    unless_count=$(grep -o '@unless' "$file" | wc -l)
    endunless_count=$(grep -o '@endunless' "$file" | wc -l)
    
    switch_count=$(grep -o '@switch' "$file" | wc -l)
    endswitch_count=$(grep -o '@endswitch' "$file" | wc -l)
    
    # Check for mismatches
    [ "$if_count" -ne "$endif_count" ] && has_error=1
    [ "$foreach_count" -ne "$endforeach_count" ] && has_error=1
    [ "$forelse_count" -ne "$endforelse_count" ] && has_error=1
    [ "$section_count" -ne "$endsection_count" ] && has_error=1
    [ "$push_count" -ne "$endpush_count" ] && has_error=1
    [ "$php_count" -ne "$endphp_count" ] && has_error=1
    [ "$unless_count" -ne "$endunless_count" ] && has_error=1
    [ "$switch_count" -ne "$endswitch_count" ] && has_error=1
    
    if [ $has_error -eq 1 ]; then
        echo -e "\n${RED}❌ $file${NC}"
        [ "$if_count" -ne "$endif_count" ] && echo "   @if/$endif: $if_count vs $endif_count"
        [ "$foreach_count" -ne "$endforeach_count" ] && echo "   @foreach/$endforeach: $foreach_count vs $endforeach_count"
        [ "$forelse_count" -ne "$endforelse_count" ] && echo "   @forelse/$endforelse: $forelse_count vs $endforelse_count"
        [ "$section_count" -ne "$endsection_count" ] && echo "   @section/$endsection: $section_count vs $endsection_count"
        [ "$push_count" -ne "$endpush_count" ] && echo "   @push/$endpush: $push_count vs $endpush_count"
        [ "$php_count" -ne "$endphp_count" ] && echo "   @php/$endphp: $php_count vs $endphp_count"
        [ "$unless_count" -ne "$endunless_count" ] && echo "   @unless/$endunless: $unless_count vs $endunless_count"
        [ "$switch_count" -ne "$endswitch_count" ] && echo "   @switch/$endswitch: $switch_count vs $endswitch_count"
    fi
done

echo -e "\n${GREEN}All blade files checked.${NC}"
