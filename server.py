from http.server import SimpleHTTPRequestHandler, HTTPServer
from urllib.parse import unquote
import os
import subprocess

# 🔒 Arquivos permitidos (whitelist)
ALLOWED_FILES = {
    "/index.html",
    "/style.css",
    "/lang.js",
    "/produtos.json",
    "/favicon.ico",
    "/1_logo.webp",
    "/logo.webp",
    "/offshore-bg.webp",
    "/fly_iso_9001.pdf",
    "/Portfolio Fly Marine 2026.pdf",
    "/admin"
}

# 📁 Diretórios liberados
ALLOWED_DIRS = {
    "/image/"
}

class StrictHandler(SimpleHTTPRequestHandler):

    def do_GET(self):
        path = self.normalize_path()

        if not self.is_allowed(path):
            print(f"🚫 Blocked access to: {path}")
            self.send_error(403, "Forbidden")
            return
        if path == "/admin":
            return self.execute_php("./admin-produtos.php")
        
        # Se for PHP, processar com interpretador
        if path.endswith('.php'):
            print(f"⚡ Executing PHP: {path}")
            self.execute_php(path)
            return
        
        self.path = path
        super().do_GET()

    def do_POST(self):
        path = self.normalize_path()

        if not self.is_allowed(path):
            print(f"🚫 Blocked POST to: {path}")
            self.send_error(403, "Forbidden")
            return

        print(f"✅ POST Allowed: {path}")
        
        # Se for /admin, executar admin-produtos.php
        if path == "/admin":
            return self.execute_php("./admin-produtos.php", is_post=True)
        
        # Se for PHP, processar com interpretador
        if path.endswith('.php'):
            self.execute_php(path, is_post=True)
            return
        
        self.send_error(405, "POST not allowed")

    def execute_php(self, path, is_post=False):
        """Executa arquivo PHP e retorna output"""
        file_path = path.lstrip('/')
        
        if not os.path.exists(file_path):
            self.send_error(404, "File not found")
            return
        
        try:
            # Preparar environment do PHP
            env = os.environ.copy()
            env['REQUEST_METHOD'] = 'POST' if is_post else 'GET'
            env['SCRIPT_FILENAME'] = os.path.abspath(file_path)
            env['SCRIPT_NAME'] = path
            env['REQUEST_URI'] = path
            
            # Ler POST data se existir
            post_data = b''
            
            if is_post and 'Content-Length' in self.headers:
                content_length = int(self.headers['Content-Length'])
                post_data = self.rfile.read(content_length)
                env['CONTENT_LENGTH'] = str(content_length)
                env['CONTENT_TYPE'] = self.headers.get('Content-Type', 'application/x-www-form-urlencoded')
            
            # Executar PHP
            result = subprocess.run(
                ['php', file_path],
                input=post_data,
                capture_output=True,
                env=env,
                cwd=os.getcwd(),
                timeout=30
            )
            
            if result.returncode != 0:
                print(f"❌ PHP Error: {result.stderr.decode('utf-8', errors='ignore')}")
                self.send_error(500, "PHP execution error")
                return
            
            output = result.stdout
            
            # Enviar resposta
            self.send_response(200)
            self.send_header('Content-Type', 'text/html; charset=utf-8')
            self.send_header('Content-Length', len(output))
            self.end_headers()
            self.wfile.write(output)
            
        except FileNotFoundError:
            print("❌ PHP interpreter not found")
            self.send_error(500, "PHP not found")
        except Exception as e:
            print(f"❌ Error: {str(e)}")
            self.send_error(500, f"Error: {str(e)}")

    def normalize_path(self):
        # remove query string
        path = self.path.split("?")[0]

        # decodifica %20 etc
        path = unquote(path)

        # garante padrão URL (não Windows)
        path = path.replace("\\", "/")

        # trata root
        if path == "/":
            return "/index.html"

        # opcional: aceita /index
        if path == "/index":
            return "/index.html"

        return path

    def is_allowed(self, path):
        # bloqueios básicos
        if ".." in path:
            return False

        if "\\" in path:
            return False

        # arquivos exatos
        if path in ALLOWED_FILES:
            return True

        # diretórios permitidos
        for d in ALLOWED_DIRS:
            if path.startswith(d):
                return True

        return False


if __name__ == "__main__":
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    PORT = 5501
    server = HTTPServer(("0.0.0.0", PORT), StrictHandler)

    print(f"🚀 Servidor seguro rodando em http://0.0.0.0:{PORT}")
    print("Pressione Ctrl+C para parar")

    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n🛑 Servidor encerrado")
        server.server_close()