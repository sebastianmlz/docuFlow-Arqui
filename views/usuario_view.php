<?php

class usuario_view
{
	public bool $mostrarDashboard;
	public bool $mostrarCrudUsuarios;
	public bool $mostrarEditarPerfil;

	public array $usuarioActual;
	public array $lista;
	public array $listaRoles;

	public bool $modoEdicion;
	public int $idSeleccionado;
	public string $nombreSeleccionado;
	public string $correoSeleccionado;
	public int $idRolSeleccionado;
	public string $tituloAccion;
	public string $colorAlerta;

	public function __construct()
	{
		$this->limpiar();
	}

	public function limpiar(): void
	{
		$this->mostrarDashboard = true;
		$this->mostrarCrudUsuarios = false;
		$this->mostrarEditarPerfil = false;

		$this->usuarioActual = array();
		$this->lista = array();
		$this->listaRoles = array();

		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->correoSeleccionado = '';
		$this->idRolSeleccionado = 0;
		$this->tituloAccion = 'Panel Principal';
		$this->colorAlerta = '#eaf2fe';
	}

	public function dashboard(string $msg): void
	{
		$this->tituloAccion = 'Panel Principal';
		$this->colorAlerta = '#eaf2fe';
		$this->render($msg);
	}

	public function editarPerfil(string $msg): void
	{
		$this->tituloAccion = 'Editar Perfil';
		$this->colorAlerta = '#fef9ea';
		$this->render($msg);
	}

	public function listar(array $lista): void
	{
		$this->lista = $lista;
		$this->tituloAccion = $this->modoEdicion ? 'Modificar Usuario Existente' : 'Registrar Nuevo Usuario';
		$this->colorAlerta = $this->modoEdicion ? '#fef9ea' : '#eaf2fe';
		$this->render('');
	}

	public function insertar(string $msg): void
	{
		$this->tituloAccion = 'Registrar Nuevo Usuario';
		$this->colorAlerta = '#eaf2fe';
		$this->render($msg);
	}

	public function actualizar(string $msg): void
	{
		$this->tituloAccion = 'Modificar Usuario Existente';
		$this->colorAlerta = '#fef9ea';
		$this->render($msg);
	}

	public function eliminar(string $msg): void
	{
		$this->tituloAccion = 'Eliminar Usuario';
		$this->colorAlerta = '#fdecec';
		$this->render($msg);
	}

	private function render(string $msg): void
	{
		$rolActual = strtolower((string)($this->usuarioActual['rol'] ?? ''));
		$nombreActual = (string)($this->usuarioActual['nombre'] ?? '');
		$correoActual = (string)($this->usuarioActual['correo'] ?? '');
		$accionCrud = $this->modoEdicion ? 'actualizar_usuario' : 'insertar_usuario';
		?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DocuFlow - Panel de Usuario</title>
	<style>
		:root {
			--bg: #eff3f8;
			--panel: #ffffff;
			--text: #1d2733;
			--muted: #5d6e81;
			--primary: #0f5dd8;
			--line: #dbe3ec;
			--ok: #1e7d3a;
			--error: #b91d1d;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Arial, sans-serif;
			background: linear-gradient(165deg, #eef3f8 0%, #f8fbff 65%, #edf3ff 100%);
			color: var(--text);
			min-height: 100vh;
		}
		.contenedor {
			max-width: 1080px;
			margin: 0 auto;
			padding: 24px;
		}
		.tarjeta {
			background: var(--panel);
			border: 1px solid var(--line);
			border-radius: 14px;
			padding: 22px;
			box-shadow: 0 10px 28px rgba(16, 24, 40, 0.06);
		}
		h1, h2, h3 {
			margin-top: 0;
		}
		.sub {
			margin: 0 0 16px;
			color: var(--muted);
		}
		p {
			color: var(--muted);
		}
		.grid {
			display: grid;
			grid-template-columns: 1fr;
			gap: 16px;
			margin-top: 16px;
		}
		@media (min-width: 900px) {
			.grid {
				grid-template-columns: 1fr 1fr;
			}
		}
		.bloque {
			border: 1px solid var(--line);
			border-radius: 12px;
			padding: 16px;
			background: #fcfdff;
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
			padding: 10px 14px;
			font-size: 14px;
			cursor: pointer;
			font-weight: 600;
		}
		.btn-primary {
			background: var(--primary);
			color: #fff;
		}
		.btn-light {
			background: #eaf1fb;
			color: #17335a;
		}
		.btn-danger {
			background: #f8e7e7;
			color: #8c1616;
		}
		label {
			display: block;
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 6px;
		}
		input,
		select {
			width: 100%;
			border: 1px solid #c8d3e0;
			border-radius: 10px;
			padding: 10px 12px;
			font-size: 14px;
			outline: none;
			background: #fff;
		}
		input:focus,
		select:focus {
			border-color: var(--primary);
		}
		.mensaje {
			border-radius: 10px;
			padding: 10px 12px;
			font-weight: 600;
			font-size: 14px;
			margin-bottom: 14px;
			border: 1px solid #c8daf7;
		}
		.tabla {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
		}
		.tabla th,
		.tabla td {
			border: 1px solid var(--line);
			padding: 10px;
			text-align: left;
			font-size: 14px;
		}
		.tabla th {
			background: #eef4fd;
		}
		.form-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 10px;
		}
		@media (max-width: 700px) {
			.form-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
</head>
<body>
	<div class="contenedor">
		<?php if ($msg !== ''): ?>
			<div class="mensaje" style="background: <?php echo htmlspecialchars($this->colorAlerta); ?>;"><?php echo htmlspecialchars($msg); ?></div>
		<?php endif; ?>

		<?php if ($this->mostrarDashboard): ?>
			<div class="tarjeta">
				<h1>Panel de usuario</h1>
				<p class="sub"><?php echo htmlspecialchars($this->tituloAccion); ?></p>
				<p>
					Hola <?php echo htmlspecialchars($nombreActual); ?>,
					tu rol actual es <strong><?php echo htmlspecialchars((string)($this->usuarioActual['rol'] ?? '')); ?></strong>.
				</p>

				<div class="acciones">
					<form method="post" action="index.php">
						<input type="hidden" name="accion_usuario" value="editar_perfil" />
						<button class="btn btn-light" type="submit">Editar perfil</button>
					</form>
					<form method="post" action="index.php">
						<input type="hidden" name="accion_usuario" value="logout" />
						<button class="btn btn-danger" type="submit">Cerrar sesion</button>
					</form>
				</div>
			</div>

			<div class="grid">
				<?php if ($rolActual === 'administrador'): ?>
					<div class="bloque">
						<h2>Acciones de Administrador</h2>
						<p>Elige la gestion que deseas abrir.</p>

						<div class="acciones">
							<form method="post" action="index.php">
								<input type="hidden" name="accion_usuario" value="ir_gestion" />
								<input type="hidden" name="destino" value="rol" />
								<button class="btn btn-primary" type="submit">Gestion de Rol</button>
							</form>
							<form method="post" action="index.php">
								<input type="hidden" name="accion_usuario" value="ir_gestion" />
								<input type="hidden" name="destino" value="periodo" />
								<button class="btn btn-primary" type="submit">Gestion de Periodos</button>
							</form>
							<form method="post" action="index.php">
								<input type="hidden" name="accion_usuario" value="ir_gestion" />
								<input type="hidden" name="destino" value="modulo" />
								<button class="btn btn-primary" type="submit">Gestion de Modulos</button>
							</form>
							<form method="post" action="index.php">
								<input type="hidden" name="accion_usuario" value="abrir_crud_usuarios" />
								<button class="btn btn-primary" type="submit">Gestion de Usuarios</button>
							</form>
							<form method="post" action="index.php">
								<input type="hidden" name="accion_usuario" value="ir_gestion" />
								<input type="hidden" name="destino" value="bloquehorario" />
								<button class="btn btn-primary" type="submit">Gestion de Bloques Horarios</button>
							</form>
							<form method="post" action="index.php">
								<input type="hidden" name="accion_usuario" value="ir_gestion" />
								<input type="hidden" name="destino" value="documento" />
								<button class="btn btn-primary" type="submit">Gestion de Documentos</button>
							</form>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($rolActual === 'ejecutivo'): ?>
					<div class="bloque">
						<h2>Acciones de Ejecutivo</h2>
						<p>Accede al modulo de citas para gestionar las entrevistas.</p>
						<form method="post" action="index.php">
							<input type="hidden" name="accion_usuario" value="ver_citas" />
							<button class="btn btn-primary" type="submit">Ir a Gestion de Citas</button>
						</form>
					</div>
				<?php endif; ?>

				<?php if ($rolActual === 'postulante'): ?>
					<div class="bloque">
						<h2>Acciones de Postulante</h2>
						<p>Accede a tus citas para revisar tu estado y proximas entrevistas.</p>
						<form method="post" action="index.php">
							<input type="hidden" name="accion_usuario" value="ver_mis_citas" />
							<button class="btn btn-primary" type="submit">Ir a Mis Citas</button>
						</form>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($this->mostrarEditarPerfil): ?>
			<div class="tarjeta">
				<h2>Editar perfil</h2>
				<p class="sub"><?php echo htmlspecialchars($this->tituloAccion); ?></p>
				<p>Si dejas la contrasena vacia, se conserva la actual.</p>
				<form method="post" action="index.php">
					<input type="hidden" name="accion_usuario" value="guardar_perfil" />

					<label for="perfil_nombre">Nombre</label>
					<input type="text" id="perfil_nombre" name="perfil_nombre" required value="<?php echo htmlspecialchars($nombreActual); ?>" />

					<label for="perfil_correo" style="margin-top:10px;">Correo</label>
					<input type="email" id="perfil_correo" name="perfil_correo" required value="<?php echo htmlspecialchars($correoActual); ?>" />

					<label for="perfil_password" style="margin-top:10px;">Nueva contrasena (opcional)</label>
					<input type="password" id="perfil_password" name="perfil_password" />

					<div class="acciones">
						<button class="btn btn-primary" type="submit">Guardar cambios</button>
					</div>
				</form>
				<form method="post" action="index.php" style="margin-top:10px;">
					<input type="hidden" name="accion_usuario" value="volver_dashboard" />
					<button class="btn btn-light" type="submit">Volver al Dashboard</button>
				</form>
			</div>
		<?php endif; ?>

		<?php if ($this->mostrarCrudUsuarios): ?>
			<div class="tarjeta">
				<h2>Gestion de Usuarios</h2>
				<p class="sub"><?php echo htmlspecialchars($this->tituloAccion); ?></p>

				<form method="post" action="index.php">
					<input type="hidden" name="accion_usuario" value="<?php echo htmlspecialchars($accionCrud); ?>" />
					<input type="hidden" name="id_usuario" value="<?php echo $this->idSeleccionado; ?>" />

					<div class="form-grid">
						<div>
							<label for="txtNombreUsuario">Nombre</label>
							<input type="text" id="txtNombreUsuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($this->nombreSeleccionado); ?>" />
						</div>
						<div>
							<label for="txtCorreoUsuario">Correo</label>
							<input type="email" id="txtCorreoUsuario" name="correo_usuario" required value="<?php echo htmlspecialchars($this->correoSeleccionado); ?>" />
						</div>
						<div>
							<label for="txtPassUsuario">Contrasena <?php echo $this->modoEdicion ? '(opcional)' : ''; ?></label>
							<input type="password" id="txtPassUsuario" name="pass_usuario" <?php echo $this->modoEdicion ? '' : 'required'; ?> />
						</div>
						<div>
							<label for="selRolUsuario">Rol</label>
							<select id="selRolUsuario" name="idrol_usuario" required>
								<option value="">Seleccione rol</option>
								<?php foreach ($this->listaRoles as $rol): ?>
									<?php
										$idRol = (int)($rol['id'] ?? 0);
										$nombreRol = (string)($rol['nombre'] ?? '');
									?>
									<option value="<?php echo $idRol; ?>" <?php echo $this->idRolSeleccionado === $idRol ? 'selected' : ''; ?>><?php echo htmlspecialchars($nombreRol); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="acciones">
						<button class="btn btn-primary" type="submit"><?php echo $this->modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
					</div>
				</form>

				<table class="tabla">
					<thead>
						<tr>
							<th>ID</th>
							<th>Nombre</th>
							<th>Correo</th>
							<th>Rol</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						<?php if (count($this->lista) === 0): ?>
							<tr>
								<td colspan="5">No hay usuarios registrados.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($this->lista as $usuario): ?>
								<?php
									$id = (int)($usuario['id'] ?? 0);
									$nombre = (string)($usuario['nombre'] ?? '');
									$correo = (string)($usuario['correo'] ?? '');
									$idRol = (int)($usuario['idrol'] ?? 0);
									$rol = (string)($usuario['rol'] ?? '');
								?>
								<tr>
									<td><?php echo $id; ?></td>
									<td><?php echo htmlspecialchars($nombre); ?></td>
									<td><?php echo htmlspecialchars($correo); ?></td>
									<td><?php echo htmlspecialchars($rol); ?></td>
									<td>
										<div class="acciones">
											<form method="post" action="index.php" style="margin:0;">
												<input type="hidden" name="accion_usuario" value="editar_usuario" />
												<input type="hidden" name="id_usuario" value="<?php echo $id; ?>" />
												<input type="hidden" name="nombre_usuario" value="<?php echo htmlspecialchars($nombre); ?>" />
												<input type="hidden" name="correo_usuario" value="<?php echo htmlspecialchars($correo); ?>" />
												<input type="hidden" name="idrol_usuario" value="<?php echo $idRol; ?>" />
												<button class="btn btn-light" type="submit">Editar</button>
											</form>

											<form method="post" action="index.php" style="margin:0;" onsubmit="return confirm('Deseas eliminar este usuario?');">
												<input type="hidden" name="accion_usuario" value="eliminar_usuario" />
												<input type="hidden" name="id_usuario" value="<?php echo $id; ?>" />
												<button class="btn btn-danger" type="submit">Eliminar</button>
											</form>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<div class="acciones">
					<form method="post" action="index.php">
						<input type="hidden" name="accion_usuario" value="volver_dashboard" />
						<button class="btn btn-light" type="submit">Volver al Dashboard</button>
					</form>
				</div>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>
		<?php
	}
}
