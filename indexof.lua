local key = KEYS[1]
local lkey = KEYS[2]
local lid = KEYS[3]
local items = redis.call('lrange', key, 0, -1)

for i=1,#items do
    if cjson.decode(items[i])[lkey] == lid then
        return i
    end
end

return -1