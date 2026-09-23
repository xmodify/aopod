package autostart

import (
	"log"
	"os"

	"golang.org/x/sys/windows/registry"
)

const (
	runKeyPath = `Software\Microsoft\Windows\CurrentVersion\Run`
	appName    = "AOPODAgent"
)

// Enable registers the current executable in HKCU\...\Run to launch on Windows startup.
func Enable() error {
	exePath, err := os.Executable()
	if err != nil {
		return err
	}

	k, err := registry.OpenKey(registry.CURRENT_USER, runKeyPath, registry.SET_VALUE)
	if err != nil {
		return err
	}
	defer k.Close()

	cmd := `"` + exePath + `" --autostart`
	if err := k.SetStringValue(appName, cmd); err != nil {
		return err
	}
	log.Printf("[AutoStart] Enabled Windows Auto-Start: %s\n", cmd)
	return nil
}

// Disable removes the executable from HKCU\...\Run.
func Disable() error {
	k, err := registry.OpenKey(registry.CURRENT_USER, runKeyPath, registry.SET_VALUE)
	if err != nil {
		return err
	}
	defer k.Close()

	if err := k.DeleteValue(appName); err != nil && err != registry.ErrNotExist {
		return err
	}
	log.Println("[AutoStart] Disabled Windows Auto-Start")
	return nil
}

// IsEnabled checks if AOPODAgent is registered in HKCU\...\Run.
func IsEnabled() bool {
	k, err := registry.OpenKey(registry.CURRENT_USER, runKeyPath, registry.QUERY_VALUE)
	if err != nil {
		return false
	}
	defer k.Close()

	val, _, err := k.GetStringValue(appName)
	return err == nil && val != ""
}

// EnsureAutoStart ensures that by default, auto-start is registered with current executable path.
func EnsureAutoStart() {
	exePath, err := os.Executable()
	if err != nil {
		return
	}
	expectedCmd := `"` + exePath + `" --autostart`

	k, err := registry.OpenKey(registry.CURRENT_USER, runKeyPath, registry.QUERY_VALUE|registry.SET_VALUE)
	if err != nil {
		return
	}
	defer k.Close()

	currentVal, _, err := k.GetStringValue(appName)
	if err != nil || currentVal != expectedCmd {
		_ = k.SetStringValue(appName, expectedCmd)
		log.Printf("[AutoStart] Registered Windows Auto-Start: %s\n", expectedCmd)
	}
}
