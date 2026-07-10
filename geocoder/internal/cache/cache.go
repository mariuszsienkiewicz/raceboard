package cache

import (
	"raceboard/geocoder/internal/domain"
	"sync"
)

type Cache struct {
	mu    sync.RWMutex // we read more often than we write
	items map[string]domain.Coordinates
}

func New() *Cache {
	return &Cache{
		items: make(map[string]domain.Coordinates),
	}
}

func (c *Cache) Get(city string) (domain.Coordinates, bool) {
	c.mu.RLock()
	defer c.mu.RUnlock()

	coords, found := c.items[city]
	return coords, found
}

func (c *Cache) Set(city string, coords domain.Coordinates) {
	c.mu.Lock()
	defer c.mu.Unlock()

	c.items[city] = coords
}
