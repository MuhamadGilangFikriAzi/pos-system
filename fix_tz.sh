PHPINI=$(php -i | grep 'Loaded Configuration File' | head -1 | awk '{print $5}')
echo "PHP ini: $PHPINI"
sed -i "s|;date.timezone =|date.timezone = Asia/Jakarta|" "$PHPINI"
php -i | grep "date.timezone" | head -3
