-- === Schema & Cleanup ===

-- ถ้ายังไม่มีตาราง ก็สร้าง
CREATE TABLE IF NOT EXISTS products (
  id          INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  description TEXT,
  price       NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  image_url   TEXT,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id               INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  customer_name    VARCHAR(200) NOT NULL,
  customer_email   VARCHAR(200) NOT NULL,
  customer_address TEXT NOT NULL,
  total_amount     NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
  id         INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  order_id   INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  product_id INTEGER NOT NULL REFERENCES products(id),
  quantity   INTEGER NOT NULL CHECK (quantity > 0),
  unit_price NUMERIC(10,2) NOT NULL
);

-- ✅ ล้างข้อมูลซ้ำใน products (เก็บชื่อแรกสุดไว้แค่ตัวเดียว)
DO $$
BEGIN
  DELETE FROM products p
  USING products q
  WHERE p.name = q.name
    AND p.ctid > q.ctid;
EXCEPTION WHEN undefined_table THEN
  -- เฉย ๆ ถ้ายังไม่มีตาราง products
  NULL;
END$$;

-- ✅ สร้าง UNIQUE constraint ถ้ายังไม่มี
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conname = 'products_name_key'
  ) THEN
    ALTER TABLE products ADD CONSTRAINT products_name_key UNIQUE (name);
  END IF;
EXCEPTION WHEN duplicate_table THEN
  NULL;
END$$;

-- ✅ Index สำหรับเรียงสินค้าตามวันที่สร้าง (ไม่สร้างซ้ำ)
CREATE INDEX IF NOT EXISTS idx_products_created_at ON products(created_at DESC);

-- === Seed แบบปลอดภัย ไม่เบิ้ล ===
INSERT INTO products (name, description, price, image_url) VALUES
('ถ้วยกาแฟ Minimal', 'ถ้วยเซรามิก โทนขาวครีม เนื้อด้าน จับถนัดมือ', 189.00, 'https://images.unsplash.com/photo-1517705008128-361805f42e86?q=80&w=1200&auto=format&fit=crop')
ON CONFLICT (name) DO NOTHING;

INSERT INTO products (name, description, price, image_url) VALUES
('กระเป๋าผ้า Everyday', 'กระเป๋าผ้าโทนอบอุ่น จุของได้มาก ใช้ได้ทุกวัน', 259.00, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=1200&auto=format&fit=crop')
ON CONFLICT (name) DO NOTHING;

INSERT INTO products (name, description, price, image_url) VALUES
('สมุดจด Dot Grid', 'สมุดจดแบบจุด ปกนุ่ม เขียนลื่น โทนมินิมอล', 149.00, 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?q=80&w=1200&auto=format&fit=crop')
ON CONFLICT (name) DO NOTHING;
