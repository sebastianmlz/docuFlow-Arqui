<?php
require_once __DIR__ . '/memento/Memento.php';
session_start();
require_once __DIR__ . '/db/db.php';

$mensaje = '';
$tipoMensaje = '';
$mostrarRegistro = false;

function limpiar($valor)
{
	return trim((string)$valor);
}

function obtenerRolPostulante($conexion)
{
	$sqlRol = "SELECT id FROM Rol WHERE LOWER(nombre) = LOWER($1) LIMIT 1";
	$resRol = pg_query_params($conexion, $sqlRol, array('Postulante'));

	if ($resRol && pg_num_rows($resRol) > 0) {
		$filaRol = pg_fetch_assoc($resRol);
		return (int)$filaRol['id'];
	}

	return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$conexion = Conectar::conexion();
	$accion = limpiar($_POST['accion'] ?? '');

	if ($accion === 'login') {
		$correo = limpiar($_POST['correo'] ?? '');
		$password = $_POST['password'] ?? '';

		if ($correo === '' || $password === '') {
			$mensaje = 'Debes ingresar correo y contrasena.';
			$tipoMensaje = 'error';
		} else {
			$sql = "
				SELECT u.id, u.nombre, u.correo, u.password, r.nombre AS rol
				FROM Usuario u
				INNER JOIN Rol r ON r.id = u.idrol
				WHERE LOWER(u.correo) = LOWER($1)
				LIMIT 1
			";
			$res = pg_query_params($conexion, $sql, array($correo));

			if ($res && pg_num_rows($res) === 1) {
				$usuario = pg_fetch_assoc($res);
				$passwordGuardado = (string)$usuario['password'];
				$loginValido = password_verify($password, $passwordGuardado) || $password === $passwordGuardado;

				if ($loginValido) {
					$_SESSION['usuario_id'] = (int)$usuario['id'];
					$_SESSION['usuario_nombre'] = $usuario['nombre'];
					$_SESSION['usuario_correo'] = $usuario['correo'];
					$_SESSION['usuario_rol'] = $usuario['rol'];

					header('Location: index.php');
					exit;
				}
			}

			$mensaje = 'Credenciales incorrectas.';
			$tipoMensaje = 'error';
		}
	}

	if ($accion === 'registro_postulante') {
		$mostrarRegistro = true;
		$nombre = limpiar($_POST['reg_nombre'] ?? '');
		$correo = limpiar($_POST['reg_correo'] ?? '');
		$password = $_POST['reg_password'] ?? '';

		if ($nombre === '' || $correo === '' || $password === '') {
			$mensaje = 'Completa nombre, correo y contrasena para registrarte.';
			$tipoMensaje = 'error';
		} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			$mensaje = 'El correo no tiene un formato valido.';
			$tipoMensaje = 'error';
		} else {
			$resExiste = pg_query_params(
				$conexion,
				"SELECT id FROM Usuario WHERE LOWER(correo) = LOWER($1) LIMIT 1",
				array($correo)
			);

			if ($resExiste && pg_num_rows($resExiste) > 0) {
				$mensaje = 'Ese correo ya esta registrado.';
				$tipoMensaje = 'error';
			} else {
				$idRolPostulante = obtenerRolPostulante($conexion);

				if ($idRolPostulante === null) {
					$mensaje = 'No existe el rol Postulante en la tabla Rol.';
					$tipoMensaje = 'error';
				} else {
					$hash = password_hash($password, PASSWORD_DEFAULT);
					$sqlInsert = "
						INSERT INTO Usuario (nombre, correo, password, idrol)
						VALUES ($1, $2, $3, $4)
					";
					$ok = pg_query_params($conexion, $sqlInsert, array($nombre, $correo, $hash, $idRolPostulante));

					if ($ok) {
						$mensaje = 'Registro exitoso. Ahora puedes iniciar sesion.';
						$tipoMensaje = 'ok';
					} else {
						$mensaje = 'No se pudo completar el registro.';
						$tipoMensaje = 'error';
					}
				}
			}
		}
	}

	if ($accion === 'logout') {
		session_unset();
		session_destroy();
		header('Location: index.php');
		exit;
	}
}

$usuarioAutenticado = isset($_SESSION['usuario_id']);
$usuarioRol = $_SESSION['usuario_rol'] ?? '';

if ($usuarioAutenticado) {
	require_once __DIR__ . '/controllers/usuario_controller.php';
	exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DocuFlow - Inicio de Sesion</title>
	<style>
		:root {
			--bg: #f3f5f7;
			--panel: #ffffff;
			--text: #1d2733;
			--muted: #607080;
			--primary: #0d5bd7;
			--danger: #bc1f1f;
			--ok: #1f7a39;
			--line: #dce3ea;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Arial, sans-serif;
			background: linear-gradient(160deg, #eef2f6 0%, #f6f8fb 55%, #e9f0fb 100%);
			color: var(--text);
			min-height: 100vh;
		}
		.contenedor {
			max-width: 1020px;
			margin: 0 auto;
			padding: 24px;
		}
		.tarjeta {
			background: var(--panel);
			border: 1px solid var(--line);
			border-radius: 14px;
			padding: 24px;
			box-shadow: 0 10px 28px rgba(16, 24, 40, 0.06);
		}
		h1, h2 {
			margin-top: 0;
			margin-bottom: 12px;
		}
		p {
			margin-top: 0;
			color: var(--muted);
		}
		.fila {
			display: grid;
			grid-template-columns: 1fr;
			gap: 14px;
		}
		@media (min-width: 900px) {
			.fila {
				grid-template-columns: 1fr 1fr;
			}
		}
		label {
			display: block;
			font-size: 14px;
			margin-bottom: 6px;
			font-weight: 600;
		}
		input {
			width: 100%;
			border: 1px solid #c8d2de;
			border-radius: 10px;
			padding: 11px 12px;
			font-size: 15px;
			outline: none;
			transition: 0.15s border-color ease;
		}
		input:focus {
			border-color: var(--primary);
		}
		.acciones {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
			margin-top: 12px;
		}
		.btn {
			border: none;
			border-radius: 10px;
			padding: 10px 16px;
			font-size: 14px;
			cursor: pointer;
			font-weight: 600;
		}
		.btn-primary {
			background: var(--primary);
			color: #fff;
		}
		.btn-light {
			background: #eef2f8;
			color: #1d2733;
		}
		.btn-danger {
			background: #f7e7e7;
			color: #8b1818;
		}
		.bloque {
			border: 1px solid var(--line);
			border-radius: 12px;
			padding: 16px;
			margin-top: 12px;
			background: #fcfdff;
		}
		.oculto { display: none; }
		.mensaje {
			margin-bottom: 14px;
			border-radius: 10px;
			padding: 10px 12px;
			font-weight: 600;
			font-size: 14px;
		}
		.mensaje.ok {
			background: #e9f7ee;
			color: var(--ok);
			border: 1px solid #b9e0c4;
		}
		.mensaje.error {
			background: #fdeeee;
			color: var(--danger);
			border: 1px solid #f3c2c2;
		}
	</style>
</head>
<body>
	<div class="contenedor">
		<?php if ($mensaje !== ''): ?>
			<div class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>">
				<?php echo htmlspecialchars($mensaje); ?>
			</div>
		<?php endif; ?>

		<?php if (!$usuarioAutenticado): ?>
			<div class="tarjeta">
				<h1>DocuFlow</h1>
				<p>Inicia sesion o registrate como postulante.</p>

				<div class="fila">
					<div class="bloque">
						<h2>Iniciar sesion</h2>
						<form method="post" action="index.php">
							<input type="hidden" name="accion" value="login" />

							<label for="correo">Correo</label>
							<input type="email" id="correo" name="correo" required />

							<label for="password" style="margin-top:10px;">Contrasena</label>
							<input type="password" id="password" name="password" required />

							<div class="acciones">
								<button class="btn btn-primary" type="submit">Login</button>
								<button class="btn btn-light" type="button" id="btnMostrarRegistro">Registrarme</button>
							</div>
						</form>
					</div>

					<div class="bloque <?php echo $mostrarRegistro ? '' : 'oculto'; ?>" id="panelRegistro">
						<h2>Registro postulante</h2>
						<p>El rol se asigna automaticamente como Postulante.</p>

						<form method="post" action="index.php">
							<input type="hidden" name="accion" value="registro_postulante" />

							<label for="reg_nombre">Nombre completo</label>
							<input type="text" id="reg_nombre" name="reg_nombre" required />

							<label for="reg_correo" style="margin-top:10px;">Correo</label>
							<input type="email" id="reg_correo" name="reg_correo" required />

							<label for="reg_password" style="margin-top:10px;">Contrasena</label>
							<input type="password" id="reg_password" name="reg_password" required />

							<div class="acciones">
								<button class="btn btn-primary" type="submit">Crear cuenta</button>
								<button class="btn btn-light" type="button" id="btnOcultarRegistro">Cancelar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="tarjeta">
				<h1>Bienvenido, <?php echo htmlspecialchars((string)$_SESSION['usuario_nombre']); ?></h1>
				<p>Rol activo: <strong><?php echo htmlspecialchars((string)$usuarioRol); ?></strong></p>

				<form method="post" action="index.php">
					<input type="hidden" name="accion" value="logout" />
					<button class="btn btn-danger" type="submit">Cerrar sesion</button>
				</form>
				<div class="bloque" style="margin-top:18px;">
					<p>Inicio de sesion correcto. En el siguiente paso redireccionaremos este acceso al modulo segun el rol.</p>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<script>
		(function () {
			var panel = document.getElementById('panelRegistro');
			var btnMostrar = document.getElementById('btnMostrarRegistro');
			var btnOcultar = document.getElementById('btnOcultarRegistro');

			if (btnMostrar && panel) {
				btnMostrar.addEventListener('click', function () {
					panel.classList.remove('oculto');
				});
			}

			if (btnOcultar && panel) {
				btnOcultar.addEventListener('click', function () {
					panel.classList.add('oculto');
				});
			}
		})();
	</script>
</body>
</html>
