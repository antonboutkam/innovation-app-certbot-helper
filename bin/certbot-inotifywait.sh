inotifywait -m "$1" -e create -e move |
while read path action file; do
  # your preferred command here
  ./certbot.php
done