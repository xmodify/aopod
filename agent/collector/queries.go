package collector

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"
	"sync"

	"aopod-agent/config"
)

var (
	queriesMutex             sync.RWMutex
	currentQueries           map[string]string
	currentVersion           string
	currentProvinceHospcodes string = "'10703', '10985', '10986', '10987', '10988', '10989', '10990'"
)

type QueryCacheFile struct {
	Version string            `json:"version"`
	Queries map[string]string `json:"queries"`
}

func init() {
	currentQueries = map[string]string{
		"opd":       defaultOpdQuery,
		"ipd":       defaultIpdQuery,
		"refer":     defaultReferQuery,
		"operation": defaultOperationQuery,
		"bed_total": defaultBedTotalQuery,
		"bed_dep":   defaultBedDepQuery,
	}
	currentVersion = "initial_default"
	InitQueriesCache()
}

func getCacheFilePath() string {
	return filepath.Join(config.GetConfigDir(), "queries_cache.json")
}

// InitQueriesCache loads cached queries from disk if present.
func InitQueriesCache() {
	path := getCacheFilePath()
	if data, err := os.ReadFile(path); err == nil {
		var cache QueryCacheFile
		if err := json.Unmarshal(data, &cache); err == nil && len(cache.Queries) > 0 {
			queriesMutex.Lock()
			for k, v := range cache.Queries {
				if strings.TrimSpace(v) != "" {
					currentQueries[k] = v
				}
			}
			currentVersion = cache.Version
			queriesMutex.Unlock()
		}
	}
}

// UpdateRemoteQueries updates the in-memory queries dynamically and saves to disk cache.
func UpdateRemoteQueries(queries map[string]string, version string) bool {
	if len(queries) == 0 {
		return false
	}

	queriesMutex.Lock()
	defer queriesMutex.Unlock()

	if version != "" && version == currentVersion {
		return false // Already latest version
	}

	changed := false
	for k, v := range queries {
		if strings.TrimSpace(v) != "" {
			currentQueries[k] = v
			changed = true
		}
	}

	if version != "" {
		currentVersion = version
	}

	// Persist to local disk cache for offline startup
	cache := QueryCacheFile{
		Version: currentVersion,
		Queries: currentQueries,
	}
	if data, err := json.MarshalIndent(cache, "", "  "); err == nil {
		_ = os.WriteFile(getCacheFilePath(), data, 0644)
	}

	return changed
}

// SetProvinceHospcodes updates the in-province hospital codes list.
func SetProvinceHospcodes(codes []string) {
	if len(codes) == 0 {
		return
	}
	quoted := make([]string, len(codes))
	for i, c := range codes {
		clean := strings.TrimSpace(c)
		quoted[i] = fmt.Sprintf("'%s'", strings.ReplaceAll(clean, "'", "''"))
	}
	queriesMutex.Lock()
	currentProvinceHospcodes = strings.Join(quoted, ", ")
	queriesMutex.Unlock()
}

// GetQueriesVersion returns current active queries version.
func GetQueriesVersion() string {
	queriesMutex.RLock()
	defer queriesMutex.RUnlock()
	return currentVersion
}
