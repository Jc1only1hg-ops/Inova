# Ineova. 

## Que es Ineova?

Ineova es una pagina que conecta estudiantes con las empresas permitiendo accesibilidad para conseguir pasantias y trabajos para que aquellos estudiantes tengan la oportunidad de trabajar respecto a su carrera.

## Como Funciona?

Ineova es una plataforma que **Usuarios** tanto **Estudiantes**, **Representates de Universidades** y **Empresas** permite conectarse a una red social en donde las **Empresas** publican ofertas de trabajo y los **Estudiantes** postulan para dichas ofertas o las **Universidades** pueden recomendar a los **Estudiantes** a las empresesas.

## Requisitos

- PHP 8.1 o superior (extensiones: `intl`, `mbstring`, `mysqli`, `pdo_mysql`, `openssl`, `fileinfo`, `curl`)
- MariaDB 10.6+ o MySQL 8.0+
- Composer

## Puesta en marcha


1. **Configurar entorno**: copiar `env` a `.env` y ajustar `app.baseURL` y las credenciales de `database.default.*`.
2. **Iniciar el servidor local**:
   ```bash
   php spark serve --host 127.0.0.1 --port 8080
   ```


