-- Runs once when the mysql container's data volume is first initialized. Creates a second,
-- isolated database for the test suites so `composer test` / `composer test:exploits`
-- never touch the live demo data in `rampart` (which migrate:fresh in tests would wipe).
CREATE DATABASE IF NOT EXISTS rampart_testing;
GRANT ALL PRIVILEGES ON rampart_testing.* TO 'rampart'@'%';
FLUSH PRIVILEGES;
