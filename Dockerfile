FROM php:8.2-apache

# 1. Instalar dependencias básicas
RUN apt-get update && apt-get install -y gnupg2 curl apt-transport-https

# 2. Agregar clave y repositorio firmado
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc -o /etc/apt/trusted.gpg.d/microsoft.asc \
    && echo "deb [arch=amd64 signed-by=/etc/apt/trusted.gpg.d/microsoft.asc] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list

# 3. Instalar dependencias, drivers y librerías
RUN apt-get update && ACCEPT_EULA=Y apt-get install -y \
    unixodbc unixodbc-dev \
    python3 python3-pip build-essential \
    msodbcsql18 mssql-tools18 \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && pip3 install --break-system-packages pandas scikit-learn joblib sqlalchemy pyodbc \
    && ln -s /usr/bin/python3 /usr/local/bin/python \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html

