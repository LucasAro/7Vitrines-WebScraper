# **7Vitrines WebScraper**

Um sistema de web scraping para autenticação e extração de dados de tabelas do site [7Vitrines CRM](https://sistema.7vitrines.com/).

---

## **Índice**

1. [Descrição do Projeto](#descrição-do-projeto)
2. [Funcionalidades](#funcionalidades)
3. [Requisitos](#requisitos)
4. [Configuração e Execução](#configuração-e-execução)
5. [Estrutura do Projeto](#estrutura-do-projeto)
6. [Uso da API](#uso-da-api)
7. [Logs](#logs)
8. [Tecnologias Utilizadas](#tecnologias-utilizadas)

---

## **Descrição do Projeto**

Este projeto foi desenvolvido para autenticar um usuário no sistema 7Vitrines CRM e extrair dados de uma tabela específica. Ele utiliza **PHP puro**, sem dependências externas, e é executado dentro de um ambiente Docker para garantir isolamento e compatibilidade.

---

## **Funcionalidades**

- Autenticação no sistema com suporte a CSRF token.
- Extração e parseamento de tabelas HTML.
- Retorno de dados no formato JSON.
- Registro de logs de erros e eventos importantes.
- Isolamento do ambiente com Docker.

---

## **Requisitos**

- **Docker** (versão 20.10+)
- **Docker Compose** (versão 1.29+)
- Porta **8000** disponível na máquina local.

---

## **Configuração e Execução**

### **1. Clonar o Repositório**
```bash
git clone https://github.com/LucasAro/7Vitrines-WebScraper.git
cd 7vitrines-webscraper
```

### **2. Construir o Contêiner**
Execute o comando abaixo para construir a imagem do Docker:
```bash
docker compose build
```

### **3. Iniciar o Contêiner**
Inicie o servidor PHP:
```bash
docker compose up
```

O servidor estará disponível em `http://localhost:8000`.

---

## **Estrutura do Projeto**

```plaintext
.
├── Dockerfile               # Configuração para o contêiner PHP
├── docker-compose.yml       # Orquestração dos serviços Docker
├── index.php                # Arquivo principal do sistema
├── app.log                  # Arquivo de log gerado automaticamente
└── README.md                # Documentação do projeto
```

---

## **Uso da API**

### **Endpoint Principal**
**URL**: `http://localhost:8000`  
**Método**: `POST`

### **Parâmetros de Entrada**

| Campo       | Tipo   | Descrição                   |
|-------------|--------|-----------------------------|
| `username`  | string | Email do usuário para login |
| `password`  | string | Senha do usuário para login |

### **Exemplo de Requisição**

#### **cURL**
```bash
curl -X POST http://localhost:8000 \
  -d "username=seu.email@exemplo.com" \
  -d "password=sua-senha"
```

#### **Postman**
1. Selecione o método **POST**.
2. Configure a URL como `http://localhost:8000`.
3. Adicione os parâmetros `username` e `password` no **Body** (form-data).

### **Exemplo de Resposta**

#### **Sucesso**
```json
{
    "data": [
        ["Coluna1", "Coluna2", "Coluna3"],
        ["Valor1", "Valor2", "Valor3"]
    ]
}
```

#### **Erro**
```json
{
    "error": "Missing username or password"
}
```

---

## **Logs**

Os logs de execução e erros são salvos no arquivo `app.log`.  
Este arquivo contém informações úteis para debug, como:

- Erros durante o login ou scraping.
- Sucesso nas operações.
- Mensagens de depuração.

### **Exemplo de Log**
```plaintext
[2024-11-19 17:09:05] [INFO] Iniciando processo para obter dados da tabela para o usuário: lucas.alexandre@teste.com
[2024-11-19 17:09:06] [DEBUG] Requisição cURL bem-sucedida para URL: https://sistema.7vitrines.com/login
[2024-11-19 17:09:06] [INFO] Autenticação realizada com sucesso para o usuário: lucas.alexandre@teste.com
[2024-11-19 17:09:07] [INFO] Dados da tabela obtidos com sucesso para o usuário: lucas.alexandre@teste.com
```

---

## **Tecnologias Utilizadas**

- **PHP 7.3**: Linguagem de programação principal.
- **Docker**: Para criar um ambiente isolado de execução.
- **cURL**: Para realizar requisições HTTP no PHP.

---

