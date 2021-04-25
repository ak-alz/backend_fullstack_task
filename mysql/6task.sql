#t1
SELECT t_info.boosterpack_id,
    COUNT(t_info.boosterpack_id) as boosterpacks_count,
    SUM(t_info.amount) as got_likes,
    SUM(t_trans.amount) as spent_money,
    DATE_FORMAT(t_info.time_created, '%Y-%m-%d %H') as dh
FROM transaction_info as t_info
LEFT JOIN transaction as t_trans
ON t_info.transaction_id = t_trans.id
WHERE t_info.time_created > DATE_SUB(NOW(), INTERVAL 31 DAY)
GROUP BY dh, t_info.boosterpack_id

#t2
SELECT t_user.id,
       t_user.wallet_balance as current_balance,
       t_user.wallet_total_refilled as total_refilled,
       t_user.wallet_likes_balance as current_likes_balance,
       SUM(t_info.amount) as got_likes,
       SUM(t_trans.amount) as spent_money
FROM transaction as t_trans
LEFT JOIN user as t_user
ON t_trans.user_id = t_user.id
INNER JOIN transaction_info as t_info
ON t_info.transaction_id = t_trans.id

WHERE t_trans.user_id = 2
