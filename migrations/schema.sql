CREATE TABLE colaboradores (
    id_colaborador INT AUTO_INCREMENT PRIMARY KEY,
    identidad VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    edad TINYINT UNSIGNED NOT NULL,
    id_tiposangre INT NOT NULL,
    id_sexo INT NOT NULL,
    nacionalidad VARCHAR(100) NOT NULL,
    id_ruta INT NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE CHECK (correo LIKE '%@%'),
    celular VARCHAR(15) NOT NULL CHECK (celular REGEXP '^[0-9]{7,15}$'),
    empleado_activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tiposangre) REFERENCES tiposangre(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_sexo) REFERENCES cat_sexo(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_ruta) REFERENCES cat_rutas(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE perfiles_laborales (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    id_colaborador INT NOT NULL,
    id_ocupacion INT NOT NULL,
    id_tipoempleado INT NOT NULL,
    id_motivo_terminacion INT NULL,
    salario DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    es_activo TINYINT(1) NOT NULL DEFAULT 1,
    firma_integridad TEXT NULL,
    FOREIGN KEY (id_colaborador) REFERENCES colaboradores(id_colaborador) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_ocupacion) REFERENCES cat_ocupaciones(C_OCUP) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_tipoempleado) REFERENCES cat_tipoempleado(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_motivo_terminacion) REFERENCES cat_motivos_terminacion(C_TERMINACION) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
