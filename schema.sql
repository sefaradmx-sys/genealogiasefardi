SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS persons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id INT UNSIGNED NULL,
  sex ENUM('M','F','U') NOT NULL DEFAULT 'U',
  living TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  primary_name VARCHAR(255) NOT NULL DEFAULT '',
  birth_date VARCHAR(64) NULL,
  birth_place VARCHAR(255) NULL,
  death_date VARCHAR(64) NULL,
  death_place VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  external_id VARCHAR(120) NULL,
  INDEX idx_persons_name (primary_name),
  INDEX idx_persons_birth (birth_date),
  INDEX idx_persons_owner (owner_id),
  INDEX idx_persons_external (owner_id, external_id),
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS person_names (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  person_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
  INDEX idx_pn_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS facts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  person_id INT UNSIGNED NOT NULL,
  type VARCHAR(64) NOT NULL,
  date_text VARCHAR(64) NULL,
  place VARCHAR(255) NULL,
  description TEXT NULL,
  FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
  INDEX idx_facts_person (person_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS relationships (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('parent-child','spouse') NOT NULL,
  person1_id INT UNSIGNED NOT NULL,
  person2_id INT UNSIGNED NOT NULL,
  date_text VARCHAR(64) NULL,
  place VARCHAR(255) NULL,
  notes TEXT NULL,
  FOREIGN KEY (person1_id) REFERENCES persons(id) ON DELETE CASCADE,
  FOREIGN KEY (person2_id) REFERENCES persons(id) ON DELETE CASCADE,
  INDEX idx_rel_p1 (person1_id),
  INDEX idx_rel_p2 (person2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sources (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  citation TEXT NULL,
  notes TEXT NULL,
  media_path VARCHAR(512) NULL,
  created_by INT UNSIGNED NULL,
  visibility ENUM('private','shared','archive') NOT NULL DEFAULT 'private',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_sources_title (title),
  INDEX idx_sources_visibility (visibility)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS source_attachments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_id INT UNSIGNED NOT NULL,
  attachable_type ENUM('person','fact','relationship') NOT NULL,
  attachable_id INT UNSIGNED NOT NULL,
  reason_statement TEXT NULL,
  FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE,
  INDEX idx_sa_attach (attachable_type, attachable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS memories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  type ENUM('photo','document','story') NOT NULL DEFAULT 'photo',
  body TEXT NULL,
  media_path VARCHAR(512) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS memory_tags (
  memory_id INT UNSIGNED NOT NULL,
  person_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (memory_id, person_id),
  FOREIGN KEY (memory_id) REFERENCES memories(id) ON DELETE CASCADE,
  FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proof_chains (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  claim TEXT NOT NULL,
  status ENUM('probado','pendiente','en_disputa') NOT NULL DEFAULT 'pendiente',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proof_chain_links (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  proof_chain_id INT UNSIGNED NOT NULL,
  source_id INT UNSIGNED NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  note TEXT NULL,
  FOREIGN KEY (proof_chain_id) REFERENCES proof_chains(id) ON DELETE CASCADE,
  FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  entity_type VARCHAR(64) NOT NULL,
  entity_id INT UNSIGNED NULL,
  action VARCHAR(64) NOT NULL,
  summary VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(64) NOT NULL PRIMARY KEY,
  `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
  user_id INT UNSIGNED NOT NULL PRIMARY KEY,
  root_person_id INT UNSIGNED NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (root_person_id) REFERENCES persons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS biographies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  person_id INT UNSIGNED NULL,
  slug VARCHAR(160) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  subtitle VARCHAR(255) NULL,
  lifespan_label VARCHAR(64) NULL,
  summary TEXT NULL,
  body_html MEDIUMTEXT NULL,
  portrait_path VARCHAR(512) NULL,
  preferred_citation VARCHAR(500) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_bio_slug (slug),
  INDEX idx_bio_featured (is_featured, published, sort_order),
  FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS biography_sources (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  biography_id INT UNSIGNED NOT NULL,
  source_id INT UNSIGNED NULL,
  citation_text TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (biography_id) REFERENCES biographies(id) ON DELETE CASCADE,
  FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE SET NULL,
  INDEX idx_bs_bio (biography_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS biography_lineage_links (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  biography_id INT UNSIGNED NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  generation_label VARCHAR(120) NULL,
  person_name VARCHAR(255) NOT NULL,
  relationship_label VARCHAR(255) NULL,
  claim_text TEXT NULL,
  status ENUM('probado','pendiente','en_disputa') NOT NULL DEFAULT 'pendiente',
  source_ids VARCHAR(255) NULL,
  proof_chain_id INT UNSIGNED NULL,
  archive_source_id INT UNSIGNED NULL,
  notes TEXT NULL,
  FOREIGN KEY (biography_id) REFERENCES biographies(id) ON DELETE CASCADE,
  INDEX idx_bll_bio (biography_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
