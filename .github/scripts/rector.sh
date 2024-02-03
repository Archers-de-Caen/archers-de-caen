RESULT=$(vendor/bin/rector process src --dry-run --no-progress-bar)

if [[ $RESULT != *"[OK] Rector is done"* ]]; then
  echo "Rector found some issues"
  echo "$RESULT"
  exit 1
else
  echo "Rector is done"
  echo "$RESULT"
  exit 0
fi
