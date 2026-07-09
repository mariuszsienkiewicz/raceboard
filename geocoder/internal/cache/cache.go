package cache

import (
	"raceboard/geocoder/internal/geocoder"
	"sync"
)

type Cache struct {
	mu    sync.RWMutex // we read more often than we write
	items map[string]geocoder.Coordinates
}

func New() *Cache {
	return &Cache{
		items: make(map[string]geocoder.Coordinates),
	}
}

func (c *Cache) Get(city string) (geocoder.Coordinates, bool) {
	c.mu.RLock()
	defer c.mu.RUnlock()

	coords, found := c.items[city]
	return coords, found
}

func (c *Cache) Set(city string, coords geocoder.Coordinates) {
	c.mu.Lock()
	defer c.mu.Unlock()

	c.items[city] = coords
}
