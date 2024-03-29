#!/bin/bash

# Update the DB dumps distributed with uzERP using the devlelopment containers

podman exec -i uzerp-postgres dropdb -U postgres demo
podman exec -i uzerp-postgres createdb -U postgres --locale=en_GB.UTF-8 'demo'
podman exec -i uzerp-postgres pg_restore -U postgres --dbname=demo < $1/schema/database/postgresql/uzerp-demo-dist.sql
podman exec -i uzerp-app-dev php vendor/bin/phinx migrate -vv -e demo -c schema/database/postgresql/phinx-dumps.yml
podman exec -i uzerp-postgres pg_dump -U postgres --dbname=demo -F c > $1/schema/database/postgresql/uzerp-demo-dist.sql
podman exec -i uzerp-postgres dropdb -U postgres demo

podman exec -i uzerp-postgres dropdb -U postgres base
podman exec -i uzerp-postgres createdb -U postgres --locale=en_GB.UTF-8 'base'
podman exec -i uzerp-postgres pg_restore -U postgres --dbname=base < $1/schema/database/postgresql/uzerp-base-dist.sql
podman exec -i uzerp-app-dev php vendor/bin/phinx migrate -vv -e base -c schema/database/postgresql/phinx-dumps.yml
podman exec -i uzerp-postgres pg_dump -U postgres --dbname=base -F c > $1/schema/database/postgresql/uzerp-base-dist.sql
podman exec -i uzerp-postgres dropdb -U postgres base

podman exec -i uzerp-postgres dropdb -U postgres starter
podman exec -i uzerp-postgres createdb -U postgres --locale=en_GB.UTF-8 'starter'
podman exec -i uzerp-postgres pg_restore -U postgres --dbname=starter < $1/schema/database/postgresql/uzerp-starter-dist.sql
podman exec -i uzerp-app-dev php vendor/bin/phinx migrate -vv -e starter -c schema/database/postgresql/phinx-dumps.yml
podman exec -i uzerp-postgres pg_dump -U postgres --dbname=starter -F c > $1/schema/database/postgresql/uzerp-starter-dist.sql
podman exec -i uzerp-postgres dropdb -U postgres starter
