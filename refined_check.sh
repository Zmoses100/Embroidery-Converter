#!/bin/bash

echo "Refined check - looking for actual @if/@endif mismatches (non-section)..."
echo "========================================================================="

# These are the files with mismatches that aren't section-related
files=(
  "resources/views/layouts/app.blade.php"
  "resources/views/layouts/guest.blade.php"
  "resources/views/convert/create.blade.php"
  "resources/views/convert/history.blade.php"
  "resources/views/convert/show.blade.php"
  "resources/views/dashboard/index.blade.php"
  "resources/views/files/index.blade.php"
  "resources/views/files/show.blade.php"
  "resources/views/notifications/index.blade.php"
  "resources/views/plans/index.blade.php"
  "resources/views/profile/edit.blade.php"
  "resources/views/admin/dashboard.blade.php"
  "resources/views/admin/plans/form.blade.php"
  "resources/views/admin/settings/index.blade.php"
  "resources/views/admin/users/index.blade.php"
)

for file in "${files[@]}"; do
  # Count @if with space (opening)
  if_count=$(grep -o '@if[[:space:]]' "$file" | wc -l)
  # Count @endif
  endif_count=$(grep -o '@endif' "$file" | wc -l)
  # Count @else (standalone)
  else_count=$(grep -o '@else[[:space:]]' "$file" | wc -l)
  # Count @elseif
  elseif_count=$(grep -o '@elseif' "$file" | wc -l)
  
  if [ "$if_count" -ne "$endif_count" ]; then
    echo -e "\n$file:"
    echo "  @if:    $if_count"
    echo "  @endif: $endif_count"
    echo "  @else:  $else_count"
    echo "  @elseif: $elseif_count"
    
    # Show lines with these directives
    echo "  Lines with @if/@endif/@else/@elseif:"
    grep -n "@if[[:space:]]\|@endif\|@else[[:space:]]\|@elseif" "$file" | head -20
  fi
done
