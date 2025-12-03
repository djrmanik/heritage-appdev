-- Heritage Family Tree - Sample Data
-- Run after schema.sql

-- ============================================
-- SAMPLE USERS
-- ============================================
-- Password for all: "password123" (hashed)
INSERT INTO users (user_id, username, email, password, role) VALUES
('550e8400-e29b-41d4-a716-446655440001', 'admin', 'admin@heritage.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('550e8400-e29b-41d4-a716-446655440002', 'john_smith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- ============================================
-- SAMPLE FAMILIES
-- ============================================
INSERT INTO families (family_id, family_name, description, created_by) VALUES
('650e8400-e29b-41d4-a716-446655440001', 'Smith Family', 'The Smith lineage traced back to 1820', '550e8400-e29b-41d4-a716-446655440001'),
('650e8400-e29b-41d4-a716-446655440002', 'Johnson Family', 'Johnson family tree from Massachusetts', '550e8400-e29b-41d4-a716-446655440001');

-- ============================================
-- SAMPLE PERSONS
-- ============================================
INSERT INTO persons (person_id, user_id, fullname, gender, birthdate, deathdate, is_alive, birthplace, notes, created_by) VALUES
('750e8400-e29b-41d4-a716-446655440001', NULL, 'Robert Smith Sr.', 'male', '1920-03-15', '1995-08-22', FALSE, 'New York, NY', 'Founder of Smith Family legacy', '550e8400-e29b-41d4-a716-446655440001'),
('750e8400-e29b-41d4-a716-446655440002', NULL, 'Margaret Smith', 'female', '1925-07-10', '2010-12-05', FALSE, 'Boston, MA', 'Spouse of Robert Smith Sr.', '550e8400-e29b-41d4-a716-446655440001'),
('750e8400-e29b-41d4-a716-446655440003', '550e8400-e29b-41d4-a716-446655440002', 'John Smith', 'male', '1950-11-20', NULL, TRUE, 'Chicago, IL', 'Son of Robert and Margaret', '550e8400-e29b-41d4-a716-446655440001'),
('750e8400-e29b-41d4-a716-446655440004', NULL, 'Emily Smith', 'female', '1978-05-14', NULL, TRUE, 'Los Angeles, CA', 'Daughter of John Smith', '550e8400-e29b-41d4-a716-446655440001'),
('750e8400-e29b-41d4-a716-446655440005', NULL, 'Michael Johnson', 'male', '1975-09-30', NULL, TRUE, 'Seattle, WA', 'Married into Smith family', '550e8400-e29b-41d4-a716-446655440001');

-- ============================================
-- LINK PERSONS TO FAMILIES
-- ============================================
INSERT INTO person_families (id, person_id, family_id, role_in_family, note) VALUES
('850e8400-e29b-41d4-a716-446655440001', '750e8400-e29b-41d4-a716-446655440001', '650e8400-e29b-41d4-a716-446655440001', 'founder', 'Patriarch'),
('850e8400-e29b-41d4-a716-446655440002', '750e8400-e29b-41d4-a716-446655440002', '650e8400-e29b-41d4-a716-446655440001', 'married_in', 'Married Robert Sr.'),
('850e8400-e29b-41d4-a716-446655440003', '750e8400-e29b-41d4-a716-446655440003', '650e8400-e29b-41d4-a716-446655440001', 'bloodline', NULL),
('850e8400-e29b-41d4-a716-446655440004', '750e8400-e29b-41d4-a716-446655440004', '650e8400-e29b-41d4-a716-446655440001', 'bloodline', NULL),
('850e8400-e29b-41d4-a716-446655440005', '750e8400-e29b-41d4-a716-446655440005', '650e8400-e29b-41d4-a716-446655440002', 'founder', 'Johnson patriarch');

-- ============================================
-- RELATIONSHIPS
-- ============================================
-- Parent-Child relationships (member_id_1 = parent, member_id_2 = child)
INSERT INTO relationships (relationship_id, member_id_1, member_id_2, relation_type, started_at) VALUES
('950e8400-e29b-41d4-a716-446655440001', '750e8400-e29b-41d4-a716-446655440001', '750e8400-e29b-41d4-a716-446655440003', 'parent', '1950-11-20'),
('950e8400-e29b-41d4-a716-446655440002', '750e8400-e29b-41d4-a716-446655440002', '750e8400-e29b-41d4-a716-446655440003', 'parent', '1950-11-20'),
('950e8400-e29b-41d4-a716-446655440003', '750e8400-e29b-41d4-a716-446655440003', '750e8400-e29b-41d4-a716-446655440004', 'parent', '1978-05-14');

-- Spouse relationship
INSERT INTO relationships (relationship_id, member_id_1, member_id_2, relation_type, started_at) VALUES
('950e8400-e29b-41d4-a716-446655440004', '750e8400-e29b-41d4-a716-446655440001', '750e8400-e29b-41d4-a716-446655440002', 'spouse', '1945-06-15');