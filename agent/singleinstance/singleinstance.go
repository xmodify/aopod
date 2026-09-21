package singleinstance

import (
	"syscall"
	"time"
	"unsafe"
)

var (
	kernel32        = syscall.NewLazyDLL("kernel32.dll")
	procCreateMutex = kernel32.NewProc("CreateMutexW")
	procCloseHandle = kernel32.NewProc("CloseHandle")
)

const (
	ERROR_ALREADY_EXISTS = 183
)

var mutexHandle uintptr

// Lock tries to create a named mutex to ensure single instance.
// Returns false if another instance is actively running.
func Lock(name string) bool {
	namePtr, err := syscall.UTF16PtrFromString(name)
	if err != nil {
		return true
	}

	for attempt := 0; attempt < 4; attempt++ {
		ret, _, callErr := procCreateMutex.Call(
			0,
			0,
			uintptr(unsafe.Pointer(namePtr)),
		)

		if ret == 0 {
			return true
		}

		if callErr != nil && callErr.(syscall.Errno) == ERROR_ALREADY_EXISTS {
			// Close our temporary handle from this attempt before sleeping
			procCloseHandle.Call(ret)

			if attempt < 3 {
				time.Sleep(800 * time.Millisecond)
				continue
			}
			return false
		}

		// Successfully acquired unique mutex
		mutexHandle = ret
		return true
	}

	return false
}

// Release closes the single instance mutex handle.
func Release() {
	if mutexHandle != 0 {
		procCloseHandle.Call(mutexHandle)
		mutexHandle = 0
	}
}

// AttachParentConsole attaches stdout/stderr to parent cmd/powershell if available.
func AttachParentConsole() {
	procAttachConsole := kernel32.NewProc("AttachConsole")
	procAttachConsole.Call(^uintptr(0)) // ATTACH_PARENT_PROCESS = -1
}
