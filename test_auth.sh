rm -f /tmp/j2.txt
TOKEN=$(curl -s -c /tmp/j2.txt http://127.0.0.1/login | grep -oP 'name="_token" value="\K[^"]+')
curl -s -L -b /tmp/j2.txt -c /tmp/j2.txt \
  -d "_token=$TOKEN&email=admin@pos.com&password=admin123" \
  http://127.0.0.1/login > /dev/null
RESP=$(curl -s -b /tmp/j2.txt "http://127.0.0.1/pos/products")
echo "Response:"
echo "$RESP" | head -50
