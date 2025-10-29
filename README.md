# bgg-collection
PHP project to manage my BGG collection

## Docker

This repository includes a simple Docker setup to run the PHP/Apache site.

Files added:

- `Dockerfile` — builds a PHP 8.2 + Apache image with GD enabled for image resizing.
- `docker-compose.yml` — service to build and run the site, exposing container port 80 on host port 8080 by default.
- `docker-entrypoint.sh` — ensures `cache/` is present and writable by the web user.

Quick start:

1. Build and start the service:

```bash
docker compose up -d --build
```

2. Visit http://localhost:8080 in your browser (or http://<server-ip>:8080).

3. Stop the service:

```bash
docker compose down
```

The `cache/` directory is mounted as a host bind so cached images persist across container restarts.

To change the host port, edit the `ports:` mapping in `docker-compose.yml` (for example to `"9090:80"`) or set up a reverse proxy on the server.

BGG API token
-------------

BoardGameGeek now requires a bearer token for API access. The container will read the token from the `BGG_API_TOKEN` environment variable. You can provide it in one of these ways:

- Export it in the shell before starting:

```bash
export BGG_API_TOKEN="your_real_token_here"
docker compose up -d --build
```

- Or create a `.env` file in the repo root with:

```
BGG_API_TOKEN=your_real_token_here
```

The `docker-compose.yml` is configured to pass the `BGG_API_TOKEN` environment variable into the container.
