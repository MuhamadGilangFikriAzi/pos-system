cd /var/www/html
rm -f /tmp/j3.txt
TOKEN=$(curl -s -c /tmp/j3.txt http://127.0.0.1/login | grep -oP 'name="_token" value="\K[^"]+')
curl -s -L -b /tmp/j3.txt -c /tmp/j3.txt \
  -d "_token=$TOKEN&email=admin@pos.com&password=admin123" \
  http://127.0.0.1/login > /dev/null

echo "=== pos/products status ==="
curl -s -o /dev/null -w "HTTP %{http_code}\n" -b /tmp/j3.txt "http://127.0.0.1/pos/products"

echo "=== Receipt page test ==="
curl -s -o /dev/null -w "Receipt %{http_code}\n" -b /tmp/j3.txt "http://127.0.0.1/pos/receipt/1"
