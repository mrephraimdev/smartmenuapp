-- ===========================================
-- SmartMenu SaaS - PostgreSQL Initialization
-- Initial database setup script
-- ===========================================

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Create indexes for better performance (will be created by Laravel migrations)
-- This file is for any custom PostgreSQL setup needed

-- Set timezone
SET timezone = 'UTC';

-- Grant permissions (adjust as needed)
GRANT ALL PRIVILEGES ON DATABASE smartmenu TO smartmenu;

-- Log successful initialization
DO $$
BEGIN
    RAISE NOTICE 'SmartMenu database initialized successfully';
END $$;
