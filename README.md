Top Level View for Icinga Web 2
===============================

## Development Environment

    ./test/setup_vendor.sh
    
    cp docker-compose.dev.yml docker-compose.yml
    # adjust to your needs
    
    docker-compose up -d
    
Then access [http://localhost:8080](http://localhost:8080).

Default admin user is `icingaadmin` with password `icinga`.

## License

    Copyright (C) 2017 Icinga Development Team <info@icinga.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
