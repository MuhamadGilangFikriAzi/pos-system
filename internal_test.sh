#!/bin/bash
# Test from inside container
cd /var/www/html

# Get login page + token
RESP=$(curl -s -c /tmp/cookie.txt http://127.0.0.1/login)
TOKEN=$(echo "$RESP" | grep -oP 'name="_token" value="\K[^"]+')
echo "Token: $TOKEN"

# Login
curl -s -L -b /tmp/cookie.txt -c /tmp/cookie.txt \
  -d "_token=$TOKEN&email=admin@pos.com&password=admin123" \
  http://127.0.0.1/login > /dev/null

# Test pages with timing
for page in pos dashboard products categories reports; do
  echo -n "$page: "
  curl -s -o /dev/null -w "%{http_code} %{time_total}s\n" -b /tmp/cookie.txt "http://127.0.0.1/$page"
done
