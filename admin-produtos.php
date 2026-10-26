<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Arquivo de credenciais
$arquivo_credenciais = './admin-credenciais.json';

// Se não existir, criar com credenciais padrão
if (!file_exists($arquivo_credenciais)) {
    $credenciais_padrao = [
        'usuario' => 'admin',
        'senha' => password_hash('123456', PASSWORD_DEFAULT)
    ];
    file_put_contents($arquivo_credenciais, json_encode($credenciais_padrao, JSON_PRETTY_PRINT));
}

$mensagem = '';
$tipo_mensagem = '';
$autenticado = isset($_SESSION['admin_autenticado']) && $_SESSION['admin_autenticado'] === true;

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    $autenticado = false;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    
    if (empty($usuario) || empty($senha)) {
        $mensagem = 'Usuário e senha são obrigatórios!';
        $tipo_mensagem = 'erro';
    } else {
        $credenciais = json_decode(file_get_contents($arquivo_credenciais), true);
        
        if ($usuario === $credenciais['usuario'] && password_verify($senha, $credenciais['senha'])) {
            $_SESSION['admin_autenticado'] = true;
            $autenticado = true;
            $mensagem = 'Login realizado com sucesso!';
            $tipo_mensagem = 'sucesso';
        } else {
            $mensagem = 'Usuário ou senha inválidos!';
            $tipo_mensagem = 'erro';
        }
    }
}

// Se não autenticado, não processa adição de produtos
if ($autenticado && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['login'])) {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    
    // Validação básica
    if (empty($nome) || empty($descricao)) {
        $mensagem = 'Nome e descrição são obrigatórios!';
        $tipo_mensagem = 'erro';
    } else {
        // Validar se há arquivos
        if (!isset($_FILES['imagens']) || empty($_FILES['imagens']['name'][0])) {
            $mensagem = 'Pelo menos uma imagem é obrigatória!';
            $tipo_mensagem = 'erro';
        } else {
            $upload_dir = './image/produtos/';
            
            // Criar diretório se não existir
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $url_imagens = [];
            $erros_upload = [];
            $sucesso = true;
            
            // Processar cada arquivo enviado
            $files = $_FILES['imagens'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $nome_arquivo = $files['name'][$i];
                    $tmp_arquivo = $files['tmp_name'][$i];
                    $tipo_arquivo = mime_content_type($tmp_arquivo);
                    
                    // Validar se é imagem
                    $tipos_validos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    
                    if (!in_array($tipo_arquivo, $tipos_validos)) {
                        $erros_upload[] = "$nome_arquivo não é uma imagem válida";
                        $sucesso = false;
                        continue;
                    }
                    
                    // Gerar nome único para o arquivo
                    $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
                    $nome_sanitizado = preg_replace('/[^a-z0-9]/i', '_', strtolower(pathinfo($nome_arquivo, PATHINFO_FILENAME)));
                    $nome_novo = $nome_sanitizado . '_' . time() . '_' . rand(1000, 9999) . '.' . $extensao;
                    $caminho_completo = $upload_dir . $nome_novo;
                    
                    // Fazer upload
                    if (move_uploaded_file($tmp_arquivo, $caminho_completo)) {
                        $url_imagens[] = './' . str_replace('\\', '/', $caminho_completo);
                    } else {
                        $erros_upload[] = "Erro ao fazer upload de $nome_arquivo";
                        $sucesso = false;
                    }
                } else if ($files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $erros_upload[] = "Erro no upload de {$files['name'][$i]}";
                    $sucesso = false;
                }
            }
            
            if ($sucesso && !empty($url_imagens)) {
                // Carregar JSON existente
                $arquivo_json = './produtos.json';
                
                if (file_exists($arquivo_json)) {
                    $conteudo_json = file_get_contents($arquivo_json);
                    $dados = json_decode($conteudo_json, true);
                } else {
                    $dados = ['produtos' => []];
                }
                
                // Encontrar o próximo ID
                $proximo_id = 1;
                if (!empty($dados['produtos'])) {
                    $ids = array_column($dados['produtos'], 'id');
                    $proximo_id = max($ids) + 1;
                }
                
                // Adicionar novo produto
                $novo_produto = [
                    'id' => $proximo_id,
                    'nome' => $nome,
                    'url_imagem' => $url_imagens,
                    'descricao' => $descricao
                ];
                
                $dados['produtos'][] = $novo_produto;
                
                // Salvar JSON formatado
                $json_formatado = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                
                if (file_put_contents($arquivo_json, $json_formatado) !== false) {
                    $mensagem = "Produto '{$nome}' adicionado com sucesso! ID: {$proximo_id}";
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao salvar dados no arquivo JSON!';
                    $tipo_mensagem = 'erro';
                }
            } else if (!empty($erros_upload)) {
                $mensagem = 'Erros no upload: ' . implode(', ', $erros_upload);
                $tipo_mensagem = 'erro';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Adicionar Produtos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a3a5a 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #1a3a5a;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }

        .mensagem {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        .mensagem.sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensagem.erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #1a3a5a;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #f37021;
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-input-wrapper {
            position: relative;
            display: block;
        }

        input[type="file"] {
            display: none;
        }

        .file-input-label {
            display: block;
            padding: 12px;
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #64748b;
            font-weight: 500;
        }

        .file-input-label:hover {
            background: #f1f5f9;
            border-color: #f37021;
            color: #f37021;
        }

        .file-list {
            margin-top: 10px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
            display: none;
        }

        .file-list.active {
            display: block;
        }

        .file-item {
            padding: 8px;
            background: white;
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .file-item.error {
            background: #fee;
            color: #c33;
        }

        .file-item.success {
            background: #efe;
            color: #3a3;
        }

        .remove-file {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 0;
            font-size: 16px;
        }

        .remove-file:hover {
            color: #f37021;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f37021 0%, #e85a0f 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(243, 112, 33, 0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .info-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }

            h1 {
                font-size: 22px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Adicionar Novo Produto</h1>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome do Produto *</label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    placeholder="Ex: Valvula Globo, Tinta Epóxi" 
                    required
                    value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                >
            </div>

            <div class="form-group">
                <label for="descricao">Descrição *</label>
                <textarea 
                    id="descricao" 
                    name="descricao" 
                    placeholder="Descreva o produto..." 
                    required
                ><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="imagens">Imagens do Produto *</label>
                <div class="file-input-wrapper">
                    <label class="file-input-label" for="imagens">
                        📸 Clique para selecionar ou arraste imagens aqui
                    </label>
                    <input 
                        type="file" 
                        id="imagens" 
                        name="imagens[]" 
                        multiple 
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                    >
                    <div class="file-list" id="fileList"></div>
                </div>
                <p class="info-text">Formatos aceitos: JPEG, PNG, WebP (máximo 5MB cada)</p>
            </div>

            <button type="submit">Adicionar Produto</button>
        </form>
    </div>

    <script>
        const fileInput = document.getElementById('imagens');
        const fileList = document.getElementById('fileList');
        const fileInputLabel = document.querySelector('.file-input-label');

        // Mostrar arquivos selecionados
        fileInput.addEventListener('change', (e) => {
            fileList.innerHTML = '';
            const files = Array.from(e.target.files);
            
            if (files.length > 0) {
                fileList.classList.add('active');
                
                files.forEach((file, index) => {
                    const isValid = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type);
                    const size = (file.size / 1024 / 1024).toFixed(2);
                    const isTooBig = file.size > 5 * 1024 * 1024;
                    
                    const fileItem = document.createElement('div');
                    fileItem.className = `file-item ${isValid && !isTooBig ? 'success' : 'error'}`;
                    
                    let status = '✓';
                    let message = `${file.name} (${size}MB)`;
                    
                    if (!isValid) {
                        status = '✗';
                        message += ' - Formato inválido';
                    } else if (isTooBig) {
                        status = '✗';
                        message += ' - Arquivo muito grande';
                    }
                    
                    fileItem.innerHTML = `
                        <span>${status} ${message}</span>
                        <button type="button" class="remove-file" onclick="removeFile(${index})">×</button>
                    `;
                    
                    fileList.appendChild(fileItem);
                });
            } else {
                fileList.classList.remove('active');
            }
        });

        // Drag and drop
        fileInputLabel.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileInputLabel.style.background = '#f1f5f9';
            fileInputLabel.style.borderColor = '#f37021';
        });

        fileInputLabel.addEventListener('dragleave', () => {
            fileInputLabel.style.background = '#f8fafc';
            fileInputLabel.style.borderColor = '#e2e8f0';
        });

        fileInputLabel.addEventListener('drop', (e) => {
            e.preventDefault();
            fileInputLabel.style.background = '#f8fafc';
            fileInputLabel.style.borderColor = '#e2e8f0';
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        });

        function removeFile(index) {
            const dataTransfer = new DataTransfer();
            const files = Array.from(fileInput.files);
            files.splice(index, 1);
            
            files.forEach(file => {
                dataTransfer.items.add(file);
            });
            
            fileInput.files = dataTransfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    </script>
</body>
</html>
