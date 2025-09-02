# Container verwalten

docker-compose up -d # Starten
docker-compose down # Stoppen
docker-compose logs -f # Logs anzeigen

# WordPress CLI

docker-compose run --rm wp-cli wp --allow-root <command>

# Datenbank-Backup

docker-compose exec -T db mysqladump -u wordpress -pwordpress_password wordpress > backup.sql

# Cache leeren

docker-compose run --rm wp-cli wp cache flush --allow-root
docker-compose exec redis redis-cli FLUSHALL
