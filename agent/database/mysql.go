package database

import (
	"database/sql"
	"fmt"
	"time"

	"aopod-agent/config"
	_ "github.com/go-sql-driver/mysql"
)

var (
	dbInstance *sql.DB
)

// GetDSN builds standard MySQL DSN string from config.
func GetDSN(dbCfg config.DatabaseConfig) string {
	return fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?charset=utf8mb4,utf8&parseTime=true&loc=Local&timeout=10s&readTimeout=60s",
		dbCfg.Username,
		dbCfg.Password,
		dbCfg.Host,
		dbCfg.Port,
		dbCfg.Database,
	)
}

// CloseDB closes active DB connection if any.
func CloseDB() {
	if dbInstance != nil {
		_ = dbInstance.Close()
		dbInstance = nil
	}
}

// Connect initializes and returns database connection.
func Connect(dbCfg config.DatabaseConfig) (*sql.DB, error) {
	dsn := GetDSN(dbCfg)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}

	db.SetMaxOpenConns(10)
	db.SetMaxIdleConns(5)
	db.SetConnMaxLifetime(5 * time.Minute)

	if err := db.Ping(); err != nil {
		db.Close()
		return nil, err
	}

	if dbInstance != nil {
		_ = dbInstance.Close()
	}
	dbInstance = db
	return db, nil
}

// GetDB returns active DB connection.
func GetDB() (*sql.DB, error) {
	if dbInstance != nil {
		if err := dbInstance.Ping(); err == nil {
			return dbInstance, nil
		}
	}
	cfg := config.Get()
	return Connect(cfg.Database)
}

// TestConnection tests the connection and returns latency & hospital info.
func TestConnection(dbCfg config.DatabaseConfig) (map[string]interface{}, error) {
	start := time.Now()
	dsn := GetDSN(dbCfg)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		return nil, err
	}
	latency := time.Since(start).Milliseconds()

	// Try reading hospital info from opdconfig
	var hcode, hname string
	_ = db.QueryRow("SELECT hospitalcode, hospitalname FROM opdconfig LIMIT 1").Scan(&hcode, &hname)

	// Check table counts
	var tableCount int
	_ = db.QueryRow("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?", dbCfg.Database).Scan(&tableCount)

	return map[string]interface{}{
		"status":        "connected",
		"latency_ms":    latency,
		"hospital_code": hcode,
		"hospital_name": hname,
		"table_count":   tableCount,
	}, nil
}
