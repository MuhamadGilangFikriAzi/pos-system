cd /var/www/html
TOKEN=$(curl -s -c /tmp/j.txt http://127.0.0.1/login | grep -oP 'name="_token" value="\K[^"]+')
curl -s -L -b /tmp/j.txt -c /tmp/j.txt \
  -d "_token=$TOKEN&email=admin@pos.com&password=admin123" \
  http://127.0.0.1/login > /dev/null

echo "=== pos/products response ==="
curl -s -b /tmp/j.txt "http://127.0.0.1/pos/products"
echo ""
echo "=== with search ==="
curl -s -b /tmp/j.txt "http://127.0.0.1/pos/products?search=indomie"
