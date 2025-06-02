CREATE DATABASE IF NOT EXISTS red_social DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE red_social;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    biografia TEXT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    contenido TEXT,
    imagen VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publicacion_id) REFERENCES publicaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (publicacion_id, usuario_id),
    FOREIGN KEY (publicacion_id) REFERENCES publicaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE amigos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1_id INT NOT NULL,
    usuario2_id INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (usuario1_id, usuario2_id),
    FOREIGN KEY (usuario1_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario2_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

    CREATE TABLE mensajes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        remitente_id INT NOT NULL,
        destinatario_id INT NOT NULL,
        mensaje TEXT NOT NULL,
        enviado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (remitente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );


CREATE TABLE solicitudes_amistad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitante_id INT NOT NULL,
    solicitado_id INT NOT NULL,
    estado ENUM('pendiente', 'aceptada', 'denegada') NOT NULL DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_solicitud (solicitante_id, solicitado_id),
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitado_id)  REFERENCES usuarios(id) ON DELETE CASCADE
);


CREATE TABLE estados_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,          -- el usuario dueño de este estado
    amigo_id INT NOT NULL,            -- el amigo con quien tiene conversación
    mensajes_no_leidos INT DEFAULT 0,
    fecha_ultimo_mensaje TIMESTAMP NULL,
    fecha_ultimo_leido TIMESTAMP NULL,
    UNIQUE KEY uk_usuario_amigo (usuario_id, amigo_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (amigo_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
