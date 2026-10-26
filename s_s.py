import http.server
import socketserver

# Define a porta em que o servidor vai rodar
PORTA = 5001


# Configura o manipulador de requisições padrão
Handler = http.server.SimpleHTTPRequestHandler

# Inicializa o servidor na porta especificada
with socketserver.TCPServer(("", PORTA), Handler) as httpd:
    print(f"Servidor rodando em: http://localhost:{PORTA}")
    print("Para desligar o servidor, pressione Ctrl+C")
    
    # Mantém o servidor ativo
    httpd.serve_forever()