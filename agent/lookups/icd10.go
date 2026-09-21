package lookups

import (
	_ "embed"
	"encoding/json"
	"fmt"
	"net/http"
	"sync"
	"time"
)

//go:embed lookup_icd10.json
var embeddedIcd10Json []byte

type Icd10Item struct {
	Icd10 string `json:"icd10"`
	PP    string `json:"pp"`
}

var (
	ppMap      = make(map[string]bool)
	ppList     = []string{}
	lookupLock sync.RWMutex
)

func init() {
	LoadEmbedded()
}

// LoadEmbedded loads the bundled ICD-10 list.
func LoadEmbedded() {
	lookupLock.Lock()
	defer lookupLock.Unlock()

	var items []Icd10Item
	if len(embeddedIcd10Json) > 0 {
		if err := json.Unmarshal(embeddedIcd10Json, &items); err == nil {
			ppMap = make(map[string]bool)
			ppList = make([]string, 0, len(items))
			for _, item := range items {
				if item.Icd10 != "" && (item.PP == "Y" || item.PP == "1") {
					ppMap[item.Icd10] = true
					ppList = append(ppList, item.Icd10)
				}
			}
		}
	}
}

// SyncFromServer downloads latest ICD-10 PP lookup from central AOPOD server.
func SyncFromServer(serverURL string) error {
	url := fmt.Sprintf("%s/api/agent/lookups/icd10", serverURL)
	client := http.Client{Timeout: 10 * time.Second}

	resp, err := client.Get(url)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("server returned status: %d", resp.StatusCode)
	}

	var result struct {
		Status string   `json:"status"`
		Data   []string `json:"data"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return err
	}

	if len(result.Data) > 0 {
		lookupLock.Lock()
		defer lookupLock.Unlock()
		ppMap = make(map[string]bool)
		ppList = result.Data
		for _, code := range result.Data {
			ppMap[code] = true
		}
	}

	return nil
}

// IsPP checks if given diagnosis is a PP diagnosis.
func IsPP(icd10 string) bool {
	lookupLock.RLock()
	defer lookupLock.RUnlock()
	return ppMap[icd10]
}

// GetPPList returns the list of all PP ICD-10 codes.
func GetPPList() []string {
	lookupLock.RLock()
	defer lookupLock.RUnlock()
	list := make([]string, len(ppList))
	copy(list, ppList)
	return list
}
