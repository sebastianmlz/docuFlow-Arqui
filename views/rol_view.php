<?php

class rol_view
{
	public bool $modoEdicion;
	public int $idSeleccionado;
	public string $nombreSeleccionado;
	public array $lista;
	public string $tituloAccion;
	public string $colorAlerta;

	public function __construct()
	{
		$this->limpiar();
	}

	public function listar(array $lista): void
	{
		$this->lista = $lista;
		$this->tituloAccion = $this->modoEdicion ? 'Modificar Rol Existente' : 'Registrar Nuevo Rol';
		$this->colorAlerta = $this->modoEdicion ? '#fef9ea' : '#eaf2fe';
		$this->render('');
	}

	public function insertar(string $msg): void
	{
		$this->tituloAccion = 'Registrar Nuevo Rol';
		$this->colorAlerta = '#eaf2fe';
		$this->render($msg);
	}

	public function actualizar(string $msg): void
	{
		$this->tituloAccion = 'Modificar Rol Existente';
		$this->colorAlerta = '#fef9ea';
		$this->render($msg);
	}

	public function eliminar(string $msg): void
	{
		$this->tituloAccion = 'Eliminar Rol';
		$this->colorAlerta = '#fdecec';
		$this->render($msg);
	}

	public function limpiar(): void
	{
		$this->modoEdicion = false;
		$this->idSeleccionado = 0;
		$this->nombreSeleccionado = '';
		$this->lista = array();
		$this->tituloAccion = 'Registrar Nuevo Rol';
		$this->colorAlerta = '#eaf2fe';
	}

	private function render(string $msg): void
	{
		$accionGuardar = $this->modoEdicion ? 'actualizar' : 'insertar';
		?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DocuFlow - Gestionar Rol</title>
	<style>
		:root {
			--bg: #f4f6f9;
			--panel: #ffffff;
			--line: #d7dfe7;
			--text: #1f2937;
			--muted: #5d6d7e;
			--primary: #0f5cc0;
			--danger: #ab1f1f;
			--ok: #226f3a;
		}
		* { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Arial, sans-serif;
			background: var(--bg);
			color: var(--text);
		}
		.contenedor {
			max-width: 980px;
			margin: 24px auto;
			padding: 0 16px;
		}
		.panel {
			background: var(--panel);
			border: 1px solid var(--line);
			border-radius: 12px;
			padding: 18px;
			box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
		}
		h1 {
			margin: 0 0 14px;
			font-size: 24px;
		}
		.sub {
			margin: 0 0 16px;
			color: var(--muted);
		}
		.mensaje {
			margin-bottom: 14px;
			padding: 10px 12px;
			border-radius: 10px;
			background: #eaf2fe;
			border: 1px solid #c8daf7;
		}
		.form-fila {
			display: grid;
			grid-template-columns: 1fr auto;
			gap: 10px;
		}
		@media (max-width: 700px) {
			.form-fila {
				grid-template-columns: 1fr;
			}
		}
		label {
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
		}
		input[type="text"] {
			width: 100%;
			padding: 10px 12px;
			border: 1px solid #c6d1dc;
			border-radius: 10px;
			font-size: 15px;
		}
		.btn {
			border: none;
			border-radius: 10px;
			padding: 10px 14px;
			font-weight: 600;
			cursor: pointer;
		}
		.btn-primary { background: var(--primary); color: #fff; }
		.btn-danger { background: #fdecec; color: var(--danger); }
		.btn-light { background: #edf1f6; color: #1f2937; }
		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 16px;
		}
		th, td {
			border-bottom: 1px solid var(--line);
			padding: 10px 8px;
			text-align: left;
			vertical-align: middle;
		}
		th {
			font-size: 13px;
			color: var(--muted);
			text-transform: uppercase;
			letter-spacing: 0.03em;
		}
		.acciones {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
		}
	</style>
</head>
<body>
	<div class="contenedor">
		<div class="panel">
			<h1>Gestionar Rol</h1>
			<p class="sub">CU1 - <?php echo htmlspecialchars($this->tituloAccion); ?></p>

			<?php if ($msg !== ''): ?>
				<div class="mensaje" style="background: <?php echo htmlspecialchars($this->colorAlerta); ?>;"><?php echo htmlspecialchars($msg); ?></div>
			<?php endif; ?>

			<form method="post" action="index.php?accion_usuario=gestion_rol">
				<input type="hidden" name="accion_rol" value="<?php echo htmlspecialchars($accionGuardar); ?>" />
				<input type="hidden" name="id_rol" value="<?php echo $this->idSeleccionado; ?>" />

				<label for="txtNombre">txtNombre</label>
				<div class="form-fila">
					<input type="text" id="txtNombre" name="nombre_rol" maxlength="60" required value="<?php echo htmlspecialchars($this->nombreSeleccionado); ?>" />
					<button id="btnGuardar" class="btn btn-primary" type="submit"><?php echo $this->modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
				</div>
			</form>

			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($this->lista) === 0): ?>
						<tr>
							<td colspan="3">No hay roles registrados.</td>
						</tr>
					<?php else: ?>
						<?php foreach ($this->lista as $rol): ?>
							<?php
								$id = (int)($rol['id'] ?? 0);
								$nombre = (string)($rol['nombre'] ?? '');
							?>
							<tr>
								<td><?php echo $id; ?></td>
								<td><?php echo htmlspecialchars($nombre); ?></td>
								<td>
									<div class="acciones">
										<form method="post" action="index.php?accion_usuario=gestion_rol" style="margin:0;">
											<input type="hidden" name="accion_rol" value="editar" />
											<input type="hidden" name="id_rol" value="<?php echo $id; ?>" />
											<input type="hidden" name="nombre_rol" value="<?php echo htmlspecialchars($nombre); ?>" />
											<button class="btn btn-light" type="submit">Editar</button>
										</form>

										<form method="post" action="index.php?accion_usuario=gestion_rol" style="margin:0;" onsubmit="return confirm('Deseas eliminar este rol?');">
											<input type="hidden" name="accion_rol" value="eliminar" />
											<input type="hidden" name="id_rol" value="<?php echo $id; ?>" />
											<button class="btn btn-danger" type="submit">Eliminar</button>
										</form>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<form method="post" action="index.php?accion_usuario=gestion_rol" style="margin-top:14px;">
				<input type="hidden" name="accion_rol" value="volver" />
				<button class="btn btn-light" type="submit">Volver</button>
			</form>
		</div>
	</div>
</body>
</html>
		<?php
	}
}

