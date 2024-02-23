# Use a imagem oficial do PHP
FROM php:8.0-cli

# Instale as dependências necessárias
RUN apt-get update && apt-get install -y \
    git \
    unzip

# Instale o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie os arquivos do projeto para o contêiner
COPY . .

# Instale as dependências do Composer
RUN composer install

# Exponha a porta 8000
EXPOSE 8000

# Inicialize o servidor embutido do PHP
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
