# Konvensi Database

| Aspek | Standar |
|-------|---------|
| Primary Key | `ulid` (string 26 char) |
| Foreign Key | `foreignUlid('xxx_id')->constrained()` |
| Timestamps | `timestamps()` + `softDeletes()` bila perlu |
| Enum | String + Check constraint (hindari native ENUM) |
| JSONB | Gunakan untuk metadata fleksibel |
| Index | Hanya untuk query aktual |
| Naming FK | `onDelete('restrict')` default, `cascade` bila jelas milik |