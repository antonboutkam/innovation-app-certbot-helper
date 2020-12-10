inotifywait -m "$1" -e create -e move |
while read path action file; do
  # your preferred command here
 ./vendor/bin/certbot.php  certificate:generate-all
done

