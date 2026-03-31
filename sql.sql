-- ============================================
-- TRAVIAN CLASSIC CLONE - COMPLETE DATABASE SCHEMA
-- ============================================

CREATE DATABASE IF NOT EXISTS travian_clone;
USE travian_clone;

-- ============================================
-- USER MANAGEMENT
-- ============================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    tribe ENUM('roman', 'teuton', 'gaul') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL DEFAULT NULL,
    beginner_protection_until DATETIME NULL DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_banned BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45),
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- ============================================
-- VILLAGES
-- ============================================

CREATE TABLE villages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    x INT NOT NULL,
    y INT NOT NULL,
    population INT DEFAULT 10,
    loyalty INT DEFAULT 100,
    is_capital BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_coordinates (x, y),
    INDEX idx_user_id (user_id),
    UNIQUE KEY unique_coordinates (x, y)
);

-- ============================================
-- RESOURCES
-- ============================================

CREATE TABLE resources (
    village_id INT PRIMARY KEY,
    wood DECIMAL(12, 2) DEFAULT 500,
    clay DECIMAL(12, 2) DEFAULT 500,
    iron DECIMAL(12, 2) DEFAULT 500,
    crop DECIMAL(12, 2) DEFAULT 500,
    wood_production INT DEFAULT 10,
    clay_production INT DEFAULT 10,
    iron_production INT DEFAULT 10,
    crop_production INT DEFAULT 5,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE
);

-- ============================================
-- BUILDINGS
-- ============================================

CREATE TABLE buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    village_id INT NOT NULL,
    building_type VARCHAR(50) NOT NULL,
    level INT DEFAULT 0,
    position INT,
    is_upgrading BOOLEAN DEFAULT FALSE,
    upgrade_complete_time DATETIME NULL DEFAULT NULL,
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_building (village_id, building_type),
    INDEX idx_village_building (village_id, building_type),
    INDEX idx_upgrading (is_upgrading)
);

-- Building queue
CREATE TABLE building_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    village_id INT NOT NULL,
    building_type VARCHAR(50) NOT NULL,
    target_level INT NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME NOT NULL,
    wood_cost INT,
    clay_cost INT,
    iron_cost INT,
    crop_cost INT,
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    INDEX idx_end_time (end_time),
    INDEX idx_village (village_id)
);

-- ============================================
-- MILITARY
-- ============================================

CREATE TABLE troops (
    village_id INT NOT NULL,
    unit_type VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 0,
    PRIMARY KEY (village_id, unit_type),
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    INDEX idx_village (village_id)
);

CREATE TABLE training_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    village_id INT NOT NULL,
    unit_type VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE CASCADE,
    INDEX idx_end_time (end_time),
    INDEX idx_processed (processed),
    INDEX idx_village (village_id)
);

CREATE TABLE troop_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_type ENUM('attack', 'support', 'return', 'raid') NOT NULL,
    from_village_id INT NOT NULL,
    to_village_id INT NOT NULL,
    user_id INT NOT NULL,
    arrival_time DATETIME NOT NULL,
    return_time DATETIME NULL DEFAULT NULL,
    units TEXT NOT NULL,
    resources_carried TEXT,
    travel_time INT DEFAULT 0,
    processed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (from_village_id) REFERENCES villages(id),
    FOREIGN KEY (to_village_id) REFERENCES villages(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_arrival_time (arrival_time),
    INDEX idx_processed (processed),
    INDEX idx_user (user_id)
);

CREATE TABLE battles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_id INT,
    attacker_village_id INT,
    defender_village_id INT,
    attacker_units TEXT,
    defender_units TEXT,
    attacker_losses TEXT,
    defender_losses TEXT,
    resources_looted TEXT,
    battle_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    winner VARCHAR(50),
    FOREIGN KEY (movement_id) REFERENCES troop_movements(id),
    INDEX idx_battle_time (battle_time),
    INDEX idx_attacker (attacker_village_id),
    INDEX idx_defender (defender_village_id)
);

-- ============================================
-- MAP
-- ============================================

CREATE TABLE map_cells (
    x INT NOT NULL,
    y INT NOT NULL,
    village_id INT NULL DEFAULT NULL,
    terrain_type VARCHAR(20) DEFAULT 'plains',
    oasis_type VARCHAR(50) NULL DEFAULT NULL,
    bonus_wood INT DEFAULT 0,
    bonus_clay INT DEFAULT 0,
    bonus_iron INT DEFAULT 0,
    bonus_crop INT DEFAULT 0,
    PRIMARY KEY (x, y),
    FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE SET NULL,
    INDEX idx_village (village_id)
);

-- ============================================
-- ALLIANCES
-- ============================================

CREATE TABLE alliances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    tag VARCHAR(10) UNIQUE NOT NULL,
    description TEXT,
    leader_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES users(id),
    INDEX idx_leader (leader_id)
);

CREATE TABLE alliance_members (
    alliance_id INT NOT NULL,
    user_id INT NOT NULL,
    rank VARCHAR(50) DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (alliance_id, user_id),
    FOREIGN KEY (alliance_id) REFERENCES alliances(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_alliance (alliance_id),
    INDEX idx_user (user_id)
);

CREATE TABLE alliance_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alliance_id INT NOT NULL,
    from_user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alliance_id) REFERENCES alliances(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_alliance (alliance_id)
);

-- ============================================
-- MESSAGES
-- ============================================

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT,
    to_user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_to_user (to_user_id, is_read),
    INDEX idx_created (created_at)
);

-- ============================================
-- QUESTS
-- ============================================

CREATE TABLE quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quest_key VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    quest_type ENUM('building', 'troop', 'resource', 'attack') NOT NULL,
    required_building_type VARCHAR(50),
    required_building_level INT,
    required_troop_type VARCHAR(50),
    required_troop_count INT,
    reward_wood INT DEFAULT 0,
    reward_clay INT DEFAULT 0,
    reward_iron INT DEFAULT 0,
    reward_crop INT DEFAULT 0,
    INDEX idx_type (quest_type)
);

CREATE TABLE user_quests (
    user_id INT NOT NULL,
    quest_id INT NOT NULL,
    progress INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    completed_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (user_id, quest_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    INDEX idx_user_completed (user_id, completed)
);

-- ============================================
-- STATISTICS & RANKINGS
-- ============================================

CREATE TABLE player_stats (
    user_id INT PRIMARY KEY,
    total_population INT DEFAULT 0,
    total_villages INT DEFAULT 1,
    total_attacks INT DEFAULT 0,
    total_wins INT DEFAULT 0,
    total_losses INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- SERVER SETTINGS
-- ============================================

CREATE TABLE server_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- VIEWS FOR RANKINGS
-- ============================================

-- View voor speler rankings (totale populatie van alle dorpen)
CREATE OR REPLACE VIEW player_rankings AS
SELECT 
    u.id,
    u.username,
    u.tribe,
    COUNT(v.id) as total_villages,
    SUM(v.population) as total_population,
    ROUND(AVG(v.loyalty), 1) as avg_loyalty,
    (SELECT COUNT(*) FROM troop_movements WHERE user_id = u.id AND movement_type = 'attack') as total_attacks,
    (SELECT COUNT(*) FROM battles WHERE winner = 'attacker' AND attacker_village_id IN (SELECT id FROM villages WHERE user_id = u.id)) as total_wins
FROM users u
LEFT JOIN villages v ON u.id = v.user_id
WHERE u.is_active = 1 AND u.is_banned = 0
GROUP BY u.id, u.username, u.tribe
ORDER BY total_population DESC;

-- View voor alliantie rankings (totale populatie van alle leden)
CREATE OR REPLACE VIEW alliance_rankings AS
SELECT 
    a.id,
    a.name,
    a.tag,
    COUNT(DISTINCT am.user_id) as total_members,
    COUNT(v.id) as total_villages,
    SUM(v.population) as total_population,
    a.created_at
FROM alliances a
LEFT JOIN alliance_members am ON a.id = am.alliance_id
LEFT JOIN users u ON am.user_id = u.id
LEFT JOIN villages v ON u.id = v.user_id
WHERE u.is_active = 1 AND u.is_banned = 0
GROUP BY a.id, a.name, a.tag, a.created_at
ORDER BY total_population DESC;

-- View voor militaire rankings (meeste troepen)
CREATE OR REPLACE VIEW military_rankings AS
SELECT 
    u.id,
    u.username,
    u.tribe,
    SUM(t.quantity) as total_troops,
    SUM(CASE WHEN t.unit_type IN ('legionnaire', 'praetorian', 'club_swinger', 'spearman', 'phalanx', 'swordsman') THEN t.quantity ELSE 0 END) as infantry,
    SUM(CASE WHEN t.unit_type IN ('paladin', 'haeduan', 'equites_legati', 'equites_imperatoris') THEN t.quantity ELSE 0 END) as cavalry
FROM users u
LEFT JOIN villages v ON u.id = v.user_id
LEFT JOIN troops t ON v.id = t.village_id
WHERE u.is_active = 1 AND u.is_banned = 0
GROUP BY u.id, u.username, u.tribe
ORDER BY total_troops DESC;

-- ============================================
-- INITIAL DATA
-- ============================================

-- Insert default quests
INSERT INTO quests (quest_key, title, description, quest_type, required_building_type, required_building_level, reward_wood, reward_clay, reward_iron, reward_crop) VALUES
('build_main_1', 'Build Main Building', 'Construct your Main Building', 'building', 'main_building', 1, 100, 100, 100, 50),
('build_woodcutter_1', 'Build Woodcutter', 'Build a Woodcutter to produce wood', 'building', 'woodcutter', 1, 150, 50, 50, 50),
('build_clay_pit_1', 'Build Clay Pit', 'Build a Clay Pit to produce clay', 'building', 'clay_pit', 1, 50, 150, 50, 50),
('build_iron_mine_1', 'Build Iron Mine', 'Build an Iron Mine to produce iron', 'building', 'iron_mine', 1, 50, 50, 150, 50),
('build_farm_1', 'Build Farm', 'Build a Farm to produce crop', 'building', 'farm', 1, 100, 100, 100, 100),
('train_troops_1', 'Train Your First Troops', 'Train 10 soldiers to defend your village', 'troop', NULL, NULL, 200, 200, 200, 100);

-- Insert server settings
INSERT INTO server_settings (setting_key, setting_value) VALUES
('server_name', 'Travian Classic'),
('server_speed', '1'),
('beginner_protection_days', '7'),
('game_start_date', NOW());

-- ============================================
-- MAP GENERATION (from -50 to 50)
-- ============================================

INSERT IGNORE INTO map_cells (x, y)
SELECT x_coord, y_coord
FROM (
    SELECT a.num as x_coord, b.num as y_coord
    FROM (
        SELECT -50 + (@row := @row + 1) as num
        FROM (SELECT @row := -51) r,
             (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) t1,
             (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) t2
        LIMIT 101
    ) a
    CROSS JOIN (
        SELECT -50 + (@row2 := @row2 + 1) as num
        FROM (SELECT @row2 := -51) r,
             (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) t1,
             (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) t2
        LIMIT 101
    ) b
) coords;

-- Add oasis variations
UPDATE map_cells 
SET oasis_type = CASE 
    WHEN MOD(x, 13) = 0 AND MOD(y, 17) = 0 THEN 'iron_oasis'
    WHEN MOD(x, 11) = 0 AND MOD(y, 13) = 0 THEN 'clay_oasis'
    WHEN MOD(x, 7) = 0 AND MOD(y, 19) = 0 THEN 'wood_oasis'
    WHEN MOD(x, 5) = 0 AND MOD(y, 23) = 0 THEN 'crop_oasis'
    ELSE NULL
END,
bonus_wood = CASE WHEN oasis_type = 'wood_oasis' THEN 25 ELSE 0 END,
bonus_clay = CASE WHEN oasis_type = 'clay_oasis' THEN 25 ELSE 0 END,
bonus_iron = CASE WHEN oasis_type = 'iron_oasis' THEN 25 ELSE 0 END,
bonus_crop = CASE WHEN oasis_type = 'crop_oasis' THEN 50 ELSE 0 END
WHERE oasis_type IS NOT NULL;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER //

-- Trigger voor player stats bij nieuwe user
CREATE TRIGGER create_player_stats_after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO player_stats (user_id, total_population, total_villages)
    VALUES (NEW.id, 10, 1);
END//

-- Trigger voor populatie update bij building upgrade
CREATE TRIGGER update_population_on_building_upgrade
AFTER UPDATE ON buildings
FOR EACH ROW
BEGIN
    IF NEW.level != OLD.level THEN
        UPDATE villages 
        SET population = (
            SELECT COALESCE(SUM(
                CASE b.building_type
                    WHEN 'main_building' THEN b.level * 5
                    WHEN 'woodcutter' THEN b.level * 1
                    WHEN 'clay_pit' THEN b.level * 1
                    WHEN 'iron_mine' THEN b.level * 1
                    WHEN 'farm' THEN b.level * 1
                    WHEN 'warehouse' THEN b.level * 2
                    WHEN 'granary' THEN b.level * 2
                    WHEN 'barracks' THEN b.level * 3
                    WHEN 'stable' THEN b.level * 3
                    WHEN 'marketplace' THEN b.level * 2
                    WHEN 'embassy' THEN b.level * 4
                    WHEN 'smithy' THEN b.level * 3
                    WHEN 'academy' THEN b.level * 4
                    WHEN 'wall' THEN b.level * 2
                    ELSE b.level * 1
                END
            ), 10)
            FROM buildings b 
            WHERE b.village_id = NEW.village_id
        )
        WHERE id = NEW.village_id;
    END IF;
END//

DELIMITER ;

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

CREATE INDEX idx_buildings_upgrading ON buildings(is_upgrading);
CREATE INDEX idx_movements_arrival ON troop_movements(arrival_time);
CREATE INDEX idx_training_end ON training_queue(end_time);
CREATE INDEX idx_messages_created ON messages(created_at);
CREATE INDEX idx_market_expires ON market_offers(expires_at);
CREATE INDEX idx_battles_time ON battles(battle_time DESC);

-- ============================================
-- END OF SCHEMA
-- ============================================