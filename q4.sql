SELECT DISTINCT oi.item_status FROM order_items oi JOIN orders o ON o.order_id = oi.order_id JOIN table_sessions ts ON ts.session_id = o.session_id;  
