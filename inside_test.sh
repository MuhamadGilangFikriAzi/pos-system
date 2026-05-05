cd /var/www/html
TOKEN=$(curl -s -c /tmp/c.txt http://127.0.0.1/login | grep -oP 'name="_token" value="\K[^"]+')
echo "Token: $TOKEN"
curl -s -L -b /tmp/c.txt -c /tmp/c.txt -d "_token=$TOKEN&email=admin@pos.com&password=admin123" http://127.0.0.1/login > /dev/null
echo "Login done"
curl -s -o /dev/null -w "pos: %{http_code} %{time_total}s\n" -b /tmp/c.txt http://127.0.0.1/pos
curl -s -o /dev/null -w "dashboard: %{http_code} %{time_total}s\n" -b /tmp/c.txt http://127.0.0.1/dashboard
curl -s -o /dev/null -w "products: %{http_code} %{time_total}s\n" -b /tmp/c.txt http://127.0.0.1/products
