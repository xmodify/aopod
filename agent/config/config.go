package config

import (
	"os"
	"path/filepath"
	"sync"

	"gopkg.in/yaml.v3"
)

const AppVersion = "1.0.0"

type Config struct {
	Hospital HospitalConfig `yaml:"hospital" json:"hospital"`
	Database DatabaseConfig `yaml:"database" json:"database"`
	Schedule ScheduleConfig `yaml:"schedule" json:"schedule"`
	Web      WebConfig      `yaml:"web" json:"web"`
}

type HospitalConfig struct {
	Code      string `yaml:"code" json:"code"`
	Name      string `yaml:"name" json:"name"`
	Token     string `yaml:"token" json:"token"`
	ServerURL string `yaml:"server_url" json:"server_url"`
}

type DatabaseConfig struct {
	Driver   string `yaml:"driver" json:"driver"`
	Host     string `yaml:"host" json:"host"`
	Port     int    `yaml:"port" json:"port"`
	Username string `yaml:"username" json:"username"`
	Password string `yaml:"password" json:"password"`
	Database string `yaml:"database" json:"database"`
}

type ScheduleConfig struct {
	Type            string `yaml:"type" json:"type"`
	IntervalHours   int    `yaml:"interval_hours" json:"interval_hours"`     // หมวดที่ 1: ทุกกี่ชั่วโมง (default 1)
	StartMinute     int    `yaml:"start_minute" json:"start_minute"`         // หมวดที่ 1: เริ่มที่นาทีที่ (default 15)
	OpdDaysBack     int    `yaml:"opd_days_back" json:"opd_days_back"`       // หมวดที่ 1: OPD ย้อนหลังกี่วัน (default 5)
	IpdDaysBack     int    `yaml:"ipd_days_back" json:"ipd_days_back"`       // หมวดที่ 1: IPD ย้อนหลังกี่วัน (default 30)
	BedIntervalMins int    `yaml:"bed_interval_mins" json:"bed_interval_mins"` // หมวดที่ 2: เตียง ทุกกี่นาที (default 15)
	IsActive        bool   `yaml:"is_active" json:"is_active"`               // สวิตช์คุมระบบ (default true)
	SyncDaysBack    int    `yaml:"sync_days_back" json:"sync_days_back"`     // backwards compatibility
	ChunkSize       int    `yaml:"chunk_size" json:"chunk_size"`             // default 200
	Threads         int    `yaml:"threads" json:"threads"`                   // default 2
}

type WebConfig struct {
	Port int  `yaml:"port" json:"port"`
	Open bool `yaml:"open_browser" json:"open_browser"`
}

var (
	currentConfig *Config
	configMutex   sync.RWMutex
)

// GetDefaultConfig returns standard default configuration.
func GetDefaultConfig() *Config {
	return &Config{
		Hospital: HospitalConfig{
			Code:      "10989",
			Name:      "รพช. หัวตะพาน",
			Token:     "",
			ServerURL: "http://127.0.0.1/aopod",
		},
		Database: DatabaseConfig{
			Driver:   "mysql",
			Host:     "127.0.0.1",
			Port:     3306,
			Username: "rims",
			Password: "your_password",
			Database: "hosxp",
		},
		Schedule: ScheduleConfig{
			Type:            "hourly",
			IntervalHours:   1,
			StartMinute:     15,
			OpdDaysBack:     5,
			IpdDaysBack:     30,
			BedIntervalMins: 15,
			IsActive:        true,
			ChunkSize:       200,
			Threads:         2,
		},
		Web: WebConfig{
			Port: 8989,
			Open: true,
		},
	}
}

// GetConfigDir returns absolute path to Roaming AppData directory for AOPOD Agent.
func GetConfigDir() string {
	baseDir, err := os.UserConfigDir()
	if err != nil {
		baseDir = os.Getenv("APPDATA")
		if baseDir == "" {
			exePath, _ := os.Executable()
			return filepath.Dir(exePath)
		}
	}
	dir := filepath.Join(baseDir, "AOPOD-Agent")
	_ = os.MkdirAll(dir, 0755)
	return dir
}

// GetConfigPath returns absolute path to config.yaml in User Roaming AppData.
func GetConfigPath() string {
	return filepath.Join(GetConfigDir(), "config.yaml")
}

// Load loads config.yaml from Roaming AppData (with migration from local dir if present).
func Load() (*Config, error) {
	configMutex.Lock()
	defer configMutex.Unlock()

	cfgPath := GetConfigPath()

	// 1. If AppData config.yaml doesn't exist, check if there's a local config.yaml beside .exe to migrate
	if _, err := os.Stat(cfgPath); os.IsNotExist(err) {
		exePath, _ := os.Executable()
		localCfgPath := filepath.Join(filepath.Dir(exePath), "config.yaml")
		
		if _, localErr := os.Stat(localCfgPath); localErr == nil {
			// Migrate local config to Roaming AppData
			if localData, readErr := os.ReadFile(localCfgPath); readErr == nil {
				cfg := GetDefaultConfig()
				if unmarshalErr := yaml.Unmarshal(localData, cfg); unmarshalErr == nil {
					_ = SaveConfig(cfg)
					currentConfig = cfg
					return cfg, nil
				}
			}
		}

		// Otherwise, create default in Roaming AppData
		cfg := GetDefaultConfig()
		_ = SaveConfig(cfg)
		currentConfig = cfg
		return cfg, nil
	}

	data, err := os.ReadFile(cfgPath)
	if err != nil {
		return nil, err
	}

	cfg := GetDefaultConfig()
	if err := yaml.Unmarshal(data, cfg); err != nil {
		return nil, err
	}

	currentConfig = cfg
	return cfg, nil
}

// Get returns the currently loaded in-memory config safely.
func Get() *Config {
	configMutex.RLock()
	defer configMutex.RUnlock()
	if currentConfig == nil {
		return GetDefaultConfig()
	}
	return currentConfig
}

// SaveConfig writes given config to config.yaml in Roaming AppData.
func SaveConfig(cfg *Config) error {
	data, err := yaml.Marshal(cfg)
	if err != nil {
		return err
	}
	return os.WriteFile(GetConfigPath(), data, 0644)
}

// Update updates the in-memory config and persists it.
func Update(cfg *Config) error {
	configMutex.Lock()
	defer configMutex.Unlock()
	currentConfig = cfg
	return SaveConfig(cfg)
}
