#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "Checking Blade directives in all .blade.php files..."
echo "========================================================"

find resources/views -name "*.blade.php" -type f | sort | while read file; do
    echo -e "\n${YELLOW}File: $file${NC}"
    
    # Count various directives
    if_count=$(grep -o '@if\s' "$file" | wc -l)
    endif_count=$(grep -o '@endif' "$file" | wc -l)
    else_count=$(grep -o '@else' "$file" | wc -l)
    elseif_count=$(grep -o '@elseif' "$file" | wc -l)
    
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
    
    case_count=$(grep -o '@switch' "$file" | wc -l)
    endcase_count=$(grep -o '@endswitch' "$file" | wc -l)
    
    # Print counts
    printf "  @if:         %2d | @endif:       %2d" "$if_count" "$endif_count"
    if [ "$if_count" -ne "$endif_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @foreach:    %2d | @endforeach:  %2d" "$foreach_count" "$endforeach_count"
    if [ "$foreach_count" -ne "$endforeach_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @forelse:    %2d | @endforelse:  %2d" "$forelse_count" "$endforelse_count"
    if [ "$forelse_count" -ne "$endforelse_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @section:    %2d | @endsection:  %2d" "$section_count" "$endsection_count"
    if [ "$section_count" -ne "$endsection_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @push:       %2d | @endpush:     %2d" "$push_count" "$endpush_count"
    if [ "$push_count" -ne "$endpush_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @php:        %2d | @endphp:      %2d" "$php_count" "$endphp_count"
    if [ "$php_count" -ne "$endphp_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @unless:     %2d | @endunless:   %2d" "$unless_count" "$endunless_count"
    if [ "$unless_count" -ne "$endunless_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
    printf "  @switch:     %2d | @endswitch:   %2d" "$case_count" "$endcase_count"
    if [ "$case_count" -ne "$endcase_count" ]; then echo -e " ${RED}MISMATCH${NC}"; else echo ""; fi
    
done

