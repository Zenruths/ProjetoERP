<?php
// Inicialize a sessão
session_start();
 
// Verifique se o usuário já está logado, se sim, redirecione-o para a página welcome.php
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: html/index.php");
    exit;
}

// Incluir arquivo de configuração
require_once "config.php";

// Inicializar variáveis
$usuario = $password = $empresaCodigo = "";
$usuario_err = $password_err = $empresaCodigo_err = "";

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar código da empresa
    if (empty(trim($_POST["empresaCodigo"]))) {
        $empresaCodigo_err = "Por favor, insira o código da empresa.";
    } else {
        $empresaCodigo = trim($_POST["empresaCodigo"]);
    }

    // Validar usuário
    if (empty(trim($_POST["usuario"]))) {
        $usuario_err = "Por favor, insira o usuário.";
    } else {
        $usuario = trim($_POST["usuario"]);
    }

    // Validar senha
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, insira sua senha.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Continuar validação se não houver erros
    if (empty($empresaCodigo_err) && empty($usuario_err) && empty($password_err)) {
        // Verificar o código da empresa
        $sql_empresa = "SELECT Codigo FROM empresas WHERE Codigo = ?";
        if ($stmt_empresa = mysqli_prepare($conection_db, $sql_empresa)) {
            mysqli_stmt_bind_param($stmt_empresa, "s", $empresaCodigo);

            if (mysqli_stmt_execute($stmt_empresa)) {
                mysqli_stmt_store_result($stmt_empresa);

                if (mysqli_stmt_num_rows($stmt_empresa) == 1) {
                    // Código da empresa válido, verificar usuário e senha
                    $sql_user = "SELECT codigo, usuario, senha, nome FROM usuarios WHERE usuario = ?";
                    if ($stmt_user = mysqli_prepare($conection_db, $sql_user)) {
                        mysqli_stmt_bind_param($stmt_user, "s", $usuario);

                        if (mysqli_stmt_execute($stmt_user)) {
                            mysqli_stmt_store_result($stmt_user);

                            if (mysqli_stmt_num_rows($stmt_user) == 1) {
                                // Verificar senha
                                mysqli_stmt_bind_result($stmt_user, $codigo, $db_usuario, $hashed_password, $nome);
                                if (mysqli_stmt_fetch($stmt_user)) {
                                    if (password_verify($password, $hashed_password)) {
                                        // Login bem-sucedido
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["codigo"] = $codigo;
                                        $_SESSION["usuario"] = $db_usuario;
                                        $_SESSION["empresaCodigo"] = $empresaCodigo;
                                        $_SESSION["nome"] = $nome;

                                        // Redirecionar para página principal
                                        header("location: html/index.php");
                                        exit();
                                    } else {
                                        // Senha incorreta
                                        $password_err = "A senha informada não é válida.";
                                    }
                                }
                            } else {
                                // Usuário não existe
                                $usuario_err = "Nenhuma conta encontrada com esse usuário.";
                            }
                        }
                        mysqli_stmt_close($stmt_user);
                    }
                } else {
                    // Empresa não existe
                    $empresaCodigo_err = "Empresa inválida ou inexistente.";
                }
            }
            mysqli_stmt_close($stmt_empresa);
        }
    }
}

if(isset($_GET['action']) && $_GET['action'] == 'buscarEmpresa') {
    $codigo = trim($_GET['codigo']);
    
    $sql = "SELECT RazaoSocial FROM empresas WHERE Codigo = ?";
    if($stmt = mysqli_prepare($conection_db, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $codigo);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $razaosocial);
                mysqli_stmt_fetch($stmt);
                echo json_encode(['success' => true, 'razaosocial' => $razaosocial]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Empresa não encontrada']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao executar a consulta']);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    exit();
}

if(isset($_GET['action']) && $_GET['action'] == 'buscarUsuario') {
    $usuario = trim($_GET['usuario']);
    
    $sql = "SELECT Nome FROM usuarios WHERE Usuario = ?";
    if($stmt = mysqli_prepare($conection_db, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $nome);
                mysqli_stmt_fetch($stmt);
                echo json_encode(['success' => true, 'nome' => $nome]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao executar a consulta']);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    exit();
}

mysqli_close($conection_db);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPatrimônio | Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/auth-login.css">
    <link rel="icon" type="image/x-icon" href="login/assets/img/favicon/logo.ico">
</head>
<body>
    <div class="login-card">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
        </div>
        
        <div class="position-relative">
            <div class="logo">
                <svg viewBox="0 0 24 24">
                    <path d="M21,16.5C21,16.88 20.79,17.21 20.47,17.38L12.57,21.82C12.41,21.94 12.21,22 12,22C11.79,22 11.59,21.94 11.43,21.82L3.53,17.38C3.21,17.21 3,16.88 3,16.5V7.5C3,7.12 3.21,6.79 3.53,6.62L11.43,2.18C11.59,2.06 11.79,2 12,2C12.21,2 12.41,2.06 12.57,2.18L20.47,6.62C20.79,6.79 21,7.12 21,7.5V16.5M12,4.15L6.04,7.5L12,10.85L17.96,7.5L12,4.15M5,15.91L11,19.29V12.58L5,9.21V15.91M19,15.91V9.21L13,12.58V19.29L19,15.91Z" />
                </svg>
                MyPatrimônio
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="logo">
                LOGIN
            </div> 
                <!-- Campo Empresa -->
                <div class="mb-4 <?= (!empty($empresaCodigo_err)) ? 'has-error' : ''; ?>">
                    <label class="form-label">EMPRESA</label>
                    <div class="row g-2">
                        <div class="col-4 col-sm-3">
                            <input type="text" name="empresaCodigo" class="form-control" id="empresaCodigo" placeholder="Código" required value="<?= $empresaCodigo; ?>">
                        </div>
                        <div class="col-8 col-sm-9">
                            <input type="text" class="form-control" id="razaoSocial" placeholder="Razão Social" readonly>
                        </div>
                    </div>
                    <span class="help-block text-danger"><?= $empresaCodigo_err; ?></span>
                </div>

                <!-- Campo Usuário -->
                <div class="mb-4 <?= (!empty($usuario_err)) ? 'has-error' : ''; ?>">
                    <label class="form-label">USUÁRIO</label>
                    <div class="row g-2">
                        <div class="col-5 col-sm-4">
                            <input type="text" name="usuario" class="form-control" id="usuario" placeholder="Usuário" required value="<?= $usuario; ?>">                       
                        </div>
                        <div class="col-7 col-sm-8">
                            <input type="text" class="form-control" id="funcionario" placeholder="Funcionário" readonly>
                        </div>
                    </div>
                    <span class="help-block text-danger"><?= $usuario_err; ?></span>
                </div>

                <!-- Campo Senha -->
            <div class="mb-4 <?= (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label class="form-label">SENHA</label>
                <div style="position: relative">  <!-- Adicionando position relative aqui -->
                    <input type="password" name="password" class="form-control" id="password" placeholder="Informe sua senha..." required>
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <span class="help-block text-danger"><?= $password_err; ?></span>
            </div>
                <button type="submit" class="btn btn-primary">Acessar</button>
                
                <div class="links">
                    <a href="auth-recupera-senha.php" class="forgot-password">Esqueceu sua senha?</a>
                    <div class="forgot-password">
                        <a class="text-muted">Ainda não possui cadastro?</span></a> <a href="auth-cadastro.php">Cadastre-se</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const eyeIcon = document.getElementById('eyeIcon');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
    // Função para buscar empresa
    $('#empresaCodigo').on('blur', function() {
        var codigo = $(this).val().trim();
        // Limpar mensagem de erro anterior
        $('.empresa-error').remove();
        
        if(codigo) {
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
                type: 'GET',
                data: { 
                    action: 'buscarEmpresa',
                    codigo: codigo 
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#razaoSocial').val(response.razaosocial);
                        $('#empresaCodigo').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#razaoSocial').val('');
                        $('#empresaCodigo').removeClass('is-valid').addClass('is-invalid');
                        // Usar um seletor mais específico para adicionar o erro
                        $('#empresaCodigo').closest('.mb-4').find('.row.g-2').after('<span class="help-block text-danger empresa-error"></span>');
                    }
                },
                error: function() {
                    $('#razaoSocial').val('');
                    $('#empresaCodigo').removeClass('is-valid').addClass('is-invalid');
                    // Usar um seletor mais específico para adicionar o erro
                    $('#empresaCodigo').closest('.mb-4').find('.row.g-2').after('<span class="help-block text-danger empresa-error">Erro ao buscar empresa</span>');
                }
            });
        } else {
            $('#razaoSocial').val('');
            $('#empresaCodigo').removeClass('is-valid is-invalid');
        }
    });

        // Script existente do toggle de senha
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const eyeIcon = document.getElementById('eyeIcon');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    });

    $(document).ready(function() {
    // Função para buscar empresa (mantém o código existente)
    $('#empresaCodigo').on('blur', function() {
        var codigo = $(this).val().trim();
        $('.empresa-error').remove();
        
        if(codigo) {
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
                type: 'GET',
                data: { 
                    action: 'buscarEmpresa',
                    codigo: codigo 
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#razaoSocial').val(response.razaosocial);
                        $('#empresaCodigo').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#razaoSocial').val('');
                        $('#empresaCodigo').removeClass('is-valid').addClass('is-invalid');
                        $('#empresaCodigo').closest('.mb-4').find('.row.g-2').after('<span class="help-block text-danger empresa-error">Empresa não encontrada</span>');
                    }
                },
                error: function() {
                    $('#razaoSocial').val('');
                    $('#empresaCodigo').removeClass('is-valid').addClass('is-invalid');
                    $('#empresaCodigo').closest('.mb-4').find('.row.g-2').after('<span class="help-block text-danger empresa-error">Erro ao buscar empresa</span>');
                }
            });
        } else {
            $('#razaoSocial').val('');
            $('#empresaCodigo').removeClass('is-valid is-invalid');
        }
    });

    // Nova função para buscar usuário
    $('#usuario').on('blur', function() {
        var usuario = $(this).val().trim();
        $('.usuario-error').remove();
        
        if(usuario) {
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
                type: 'GET',
                data: { 
                    action: 'buscarUsuario',
                    usuario: usuario 
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#funcionario').val(response.nome);
                        $('#usuario').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#funcionario').val('');
                        $('#usuario').removeClass('is-valid').addClass('is-invalid');
                        $('#usuario').closest('.mb-4').find('.row.g-2').after('<span class="help-block text-danger usuario-error">Usuário não encontrado</span>');
                    }
                },
                error: function() {
                    $('#funcionario').val('');
                    $('#usuario').removeClass('is-valid').addClass('is-invalid');
                    $('#usuario').closest('.mb-4').find('.row.g-2').after('<span class="help-block text-danger usuario-error">Erro ao buscar usuário</span>');
                }
            });
        } else {
            $('#funcionario').val('');
            $('#usuario').removeClass('is-valid is-invalid');
        }
    });

    // Mantém o código existente do toggle de senha
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const eyeIcon = document.getElementById('eyeIcon');

    passwordToggle.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            `;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        }
    });
});

// NOVO
// Adicione este código para melhorar a experiência do usuário
$(document).ready(function() {
    // Prevenir multiple submits
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        // Adicionar spinner ao botão
        const originalText = $(this).find('button[type="submit"]').text();
        $(this).find('button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Aguarde...');
        
        // Reabilitar botão após 3 segundos (caso ocorra algum erro)
        setTimeout(() => {
            $(this).find('button[type="submit"]').prop('disabled', false).text(originalText);
        }, 3000);
    });

    // Limpar campos relacionados quando o principal é limpo
    $('#empresaCodigo').on('input', function() {
        if (!$(this).val()) {
            $('#razaoSocial').val('');
            $(this).removeClass('is-valid is-invalid');
        }
    });

    $('#usuario').on('input', function() {
        if (!$(this).val()) {
            $('#funcionario').val('');
            $(this).removeClass('is-valid is-invalid');
        }
    });
});
</script>

<script>
    document.getElementById('empresaCodigo').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

</body>
</html>