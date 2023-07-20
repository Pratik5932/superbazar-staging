		==Review Api==

==Installation and Configuration==

1. Download the package
2. Copy and paste under Magento root directory, folder structure should be like this
3. magento_root/app/code/Ipragmatech/Ipreview.
4. Go to the Magento root directory from your terminal
5. Run the command : sudo php bin/magento setup:upgrade
6. Delete the di, generation and cache from var/
7. Run the command: sudo php bin/magento setup:di:compile
8. Run the command: sudo php bin/magento cache:clean
9. Give the read and execute permission var/di, var/generation, var/cache


==urls==
1. Get review of product

url: http://mymagento.com/V1/review/reviews/{productId}
type:GET
response: [
              {
                  "avg_rating_percent": "90",
                  "count": 2,
                  "reviews": [
                      {
                          "review_id": "9",
                          "created_at": "2016-11-07 10:59:58",
                          "entity_id": "1",
                          "entity_pk_value": "2",
                          "status_id": "1",
                          "detail_id": "9",
                          "title": "awesome for going back and forth",
                          "detail": "This is awesome for going back and forth to class. I live off campus and it's a longer walk, but this pack fits comfortably and I can even store my laptop in the main compartment.",
                          "nickname": "Gaston",
                          "customer_id": null,
                          "entity_code": "product",
                          "rating_votes": [
                              {
                                  "vote_id": "9",
                                  "option_id": "20",
                                  "remote_ip": "127.0.0.1",
                                  "remote_ip_long": "2130706433",
                                  "customer_id": null,
                                  "entity_pk_value": "2",
                                  "rating_id": "4",
                                  "review_id": "9",
                                  "percent": "100",
                                  "value": "5",
                                  "rating_code": "Rating",
                                  "store_id": "1"
                              }
                          ]
                      },
                      {
                          "review_id": "10",
                          "created_at": "2016-11-07 10:59:58",
                          "entity_id": "1",
                          "entity_pk_value": "2",
                          "status_id": "1",
                          "detail_id": "10",
                          "title": "comfy and i don't feel like a loser",
                          "detail": "comfy and i don't feel like a loser carrying it.",
                          "nickname": "Issac",
                          "customer_id": null,
                          "entity_code": "product",
                          "rating_votes": [
                              {
                                  "vote_id": "10",
                                  "option_id": "19",
                                  "remote_ip": "127.0.0.1",
                                  "remote_ip_long": "2130706433",
                                  "customer_id": null,
                                  "entity_pk_value": "2",
                                  "rating_id": "4",
                                  "review_id": "10",
                                  "percent": "80",
                                  "value": "4",
                                  "rating_code": "Rating",
                                  "store_id": "1"
                              }
                          ]
                      }
                  ]
              }
          ]
2. Get available ratings
url: http://mymagento.com/rest/V1/rating/ratings/{storeId}
type:GET
response: [
             {
                 "rating_id": "3",
                 "entity_id": "1",
                 "rating_code": "Price",
                 "position": "0",
                 "is_active": "1",
                 "store_id": "1"
             },
             {
                 "rating_id": "4",
                 "entity_id": "1",
                 "rating_code": "Rating",
                 "position": "0",
                 "is_active": "1",
                 "store_id": "1"
             }
         ]
3. Add review by customer

url: http://mymagento.com/V1/review/mine/post
type:POST
payload: {
         	"productId": "10",
         	"nickname": "Mann",
         	"title": "Cool, Nice product",
         	"detail": "This is nice product. I recommended this product.",
         	"ratingData": [{
         		"rating_id": "3",
         		"ratingCode": "price",
         		"ratingValue": "5"
         	}, {
         		"rating_id": "4",
         		"ratingCode": "Rating",
         		"ratingValue": "2"
         	}],
         	"storeId": "1"
         }

3. Add review by guest user

url: http://mymagento.com/V1/review/guest/post
type:POST
payload: {
         	"productId": "10",
         	"nickname": "Mann",
         	"title": "Cool, Nice product",
         	"detail": "This is nice product. I recommended this product.",
         	"ratingData": [{
         		"rating_id": "3",
         		"ratingCode": "price",
         		"ratingValue": "5"
         	}, {
         		"rating_id": "4",
         		"ratingCode": "Rating",
         		"ratingValue": "2"
         	}],
         	"storeId": "1"
         }