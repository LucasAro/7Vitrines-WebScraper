FROM php:7.3-cli

RUN apt-get update && apt-get install -y \
	libcurl4-openssl-dev \
	unzip \
	curl \
	&& docker-php-ext-install curl

# Configurar o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto para o contêiner
COPY . .

# Comando para rodar o servidor embutido do PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]
