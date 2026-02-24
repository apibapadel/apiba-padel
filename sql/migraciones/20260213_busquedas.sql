-- APiBA Pádel - mejoras para buscador y filtros (Mercado Libre UI)
-- Fecha: 2026-02-13
-- Nota: si algún índice ya existe, MySQL puede tirar "Duplicate key name".
-- En ese caso, podés omitir esa línea.

-- TORNEOS (busca por nombre/sede/categoría y filtra por estado)
ALTER TABLE torneos
  ADD INDEX idx_torneos_nombre (nombre),
  ADD INDEX idx_torneos_categoria (categoria),
  ADD INDEX idx_torneos_sede (sede),
  ADD INDEX idx_torneos_estado (estado);

-- NOTICIAS (busca por titulo/contenido y filtra por activa/destacada)
ALTER TABLE noticias
  ADD INDEX idx_noticias_titulo (titulo),
  ADD INDEX idx_noticias_activa (activa),
  ADD INDEX idx_noticias_destacada (destacada),
  ADD INDEX idx_noticias_fecha (fecha_publicacion);
