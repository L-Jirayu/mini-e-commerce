-- สร้าง DB/User ครั้งแรก (ทำใน pgAdmin ก็ได้)
-- CREATE DATABASE mini_shop;
-- CREATE USER mini_user WITH PASSWORD 'mini_pass';
-- GRANT ALL PRIVILEGES ON DATABASE mini_shop TO mini_user;

-- จากนั้น \c mini_shop แล้วค่อยรันสคีมาด้านล่าง

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

-- seed
INSERT INTO products (name, description, price, image_url) VALUES
('ถ้วยกาแฟ Minimal', 'ถ้วยเซรามิก โทนขาวครีม เนื้อด้าน จับถนัดมือ', 189.00, 'https://images.unsplash.com/photo-1517705008128-361805f42e86?q=80&w=1200&auto=format&fit=crop'),
('กระเป๋าผ้า Everyday', 'กระเป๋าผ้าโทนอบอุ่น จุของได้มาก ใช้ได้ทุกวัน', 259.00, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=1200&auto=format&fit=crop'),
('สมุดจด Dot Grid', 'สมุดจดแบบจุด ปกนุ่ม เขียนลื่น โทนมินิมอล', 149.00, 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?q=80&w=1200&auto=format&fit=crop');
