<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Source;

class BusinessIndustry extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Possible environment types.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'value' => '',
                    'label' => __("Please Select"),
                ],
                ['value' => '7623', 'label' => __('A/C, Refrigeration Repair')],
                ['value' => '8931', 'label' => __('Accounting/Bookkeeping Services')],
                ['value' => '7311', 'label' => __('Advertising Services')],
                ['value' => '0763', 'label' => __('Agricultural Cooperative')],
                ['value' => '4511', 'label' => __('Airlines, Air Carriers')],
                ['value' => '4582', 'label' => __('Airports, Flying Fields')],
                ['value' => '4119', 'label' => __('Ambulance Services')],
                ['value' => '7996', 'label' => __('Amusement Parks/Carnivals')],
                ['value' => '5937', 'label' => __('Antique Reproductions')],
                ['value' => '5932', 'label' => __('Antique Shops')],
                ['value' => '7998', 'label' => __('Aquariums')],
                ['value' => '8911', 'label' => __('Architectural/Surveying Services')],
                ['value' => '5971', 'label' => __('Art Dealers and Galleries')],
                ['value' => '5970', 'label' => __('Artists Supply and Craft Shops')],
                ['value' => '7531', 'label' => __('Auto Body Repair Shops')],
                ['value' => '7535', 'label' => __('Auto Paint Shops')],
                ['value' => '7538', 'label' => __('Auto Service Shops')],
                ['value' => '5531', 'label' => __('Auto and Home Supply Stores')],
                ['value' => '6011', 'label' => __('Automated Cash Disburse')],
                ['value' => '5542', 'label' => __('Automated Fuel Dispensers')],
                ['value' => '8675', 'label' => __('Automobile Associations')],
                ['value' => '5533', 'label' => __('Automotive Parts and Accessories Stores')],
                ['value' => '5532', 'label' => __('Automotive Tire Stores')],
                ['value' => '9223', 'label' => __('Bail and Bond Payments')],
                ['value' => '5462', 'label' => __('Bakeries')],
                ['value' => '7929', 'label' => __('Bands, Orchestras')],
                ['value' => '7230', 'label' => __('Barber and Beauty Shops')],
                ['value' => '7995', 'label' => __('Betting/Casino Gambling')],
                ['value' => '5940', 'label' => __('Bicycle Shops')],
                ['value' => '7932', 'label' => __('Billiard/Pool Establishments')],
                ['value' => '5551', 'label' => __('Boat Dealers')],
                ['value' => '4457', 'label' => __('Boat Rentals and Leases')],
                ['value' => '5942', 'label' => __('Book Stores')],
                ['value' => '5192', 'label' => __('Books, Periodicals, and Newspapers')],
                ['value' => '7933', 'label' => __('Bowling Alleys')],
                ['value' => '4131', 'label' => __('Bus Lines')],
                ['value' => '8244', 'label' => __('Business/Secretarial Schools')],
                ['value' => '7278', 'label' => __('Buying/Shopping Services')],
                ['value' => '4899', 'label' => __('Cable, Satellite, and Other Pay Television and Radio')],
                ['value' => '5946', 'label' => __('Camera and Photographic Supply Stores')],
                ['value' => '5441', 'label' => __('Candy, Nut, and Confectionery Stores')],
                ['value' => '7512', 'label' => __('Car Rental Agencies')],
                ['value' => '7542', 'label' => __('Car Washes')],
                ['value' => '5511', 'label' => __(
                    'Car and Truck Dealers (New & Used) Sales, Service, Repairs Parts and Leasing'
                )
                ],
                ['value' => '5521', 'label' => __(
                    'Car and Truck Dealers (Used Only) Sales, Service, Repairs Parts and Leasing'
                )
                ],
                ['value' => '1750', 'label' => __('Carpentry Services')],
                ['value' => '7217', 'label' => __('Carpet/Upholstery Cleaning')],
                ['value' => '5811', 'label' => __('Caterers')],
                ['value' => '8398', 'label' => __('Charitable and Social Service Organizations - Fundraising')],
                ['value' => '5169', 'label' => __('Chemicals and Allied Products (Not Elsewhere Classified)')],
                ['value' => '5641', 'label' => __('Chidrens and Infants Wear Stores')],
                ['value' => '8351', 'label' => __('Child Care Services')],
                ['value' => '8049', 'label' => __('Chiropodists, Podiatrists')],
                ['value' => '8041', 'label' => __('Chiropractors')],
                ['value' => '5993', 'label' => __('Cigar Stores and Stands')],
                ['value' => '8641', 'label' => __('Civic, Social, Fraternal Associations')],
                ['value' => '7349', 'label' => __('Cleaning and Maintenance')],
                ['value' => '7296', 'label' => __('Clothing Rental')],
                ['value' => '8220', 'label' => __('Colleges, Universities')],
                ['value' => '5046', 'label' => __('Commercial Equipment (Not Elsewhere Classified)')],
                ['value' => '5139', 'label' => __('Commercial Footwear')],
                ['value' => '7333', 'label' => __('Commercial Photography, Art and Graphics')],
                ['value' => '4111', 'label' => __('Commuter Transport, Ferries')],
                ['value' => '4816', 'label' => __('Computer Network Services')],
                ['value' => '7372', 'label' => __('Computer Programming')],
                ['value' => '7379', 'label' => __('Computer Repair')],
                ['value' => '5734', 'label' => __('Computer Software Stores')],
                ['value' => '5045', 'label' => __('Computers, Peripherals, and Software')],
                ['value' => '1771', 'label' => __('Concrete Work Services')],
                ['value' => '5039', 'label' => __('Construction Materials (Not Elsewhere Classified)')],
                ['value' => '7392', 'label' => __('Consulting, Public Relations')],
                ['value' => '8241', 'label' => __('Correspondence Schools')],
                ['value' => '5977', 'label' => __('Cosmetic Stores')],
                ['value' => '7277', 'label' => __('Counseling Services')],
                ['value' => '7997', 'label' => __('Country Clubs')],
                ['value' => '4215', 'label' => __('Courier Services')],
                ['value' => '9211', 'label' => __('Court Costs, Including Alimony and Child Support - Courts of Law')],
                ['value' => '7321', 'label' => __('Credit Reporting Agencies')],
                ['value' => '4411', 'label' => __('Cruise Lines')],
                ['value' => '5451', 'label' => __('Dairy Products Stores')],
                ['value' => '7911', 'label' => __('Dance Hall, Studios, Schools')],
                ['value' => '7273', 'label' => __('Dating/Escort Services')],
                ['value' => '8021', 'label' => __('Dentists, Orthodontists')],
                ['value' => '5311', 'label' => __('Department Stores')],
                ['value' => '7393', 'label' => __('Detective Agencies')],
                ['value' => '5817', 'label' => __('Digital Goods – Applications (Excludes Games)')],
                ['value' => '5964', 'label' => __('Direct Marketing - Catalog Merchant')],
                ['value' => '5965', 'label' => __('Direct Marketing - Combination Catalog and Retail Merchant')],
                ['value' => '5967', 'label' => __('Direct Marketing - Inbound Telemarketing')],
                ['value' => '5960', 'label' => __('Direct Marketing - Insurance Services')],
                ['value' => '5969', 'label' => __('Direct Marketing - Other')],
                ['value' => '5966', 'label' => __('Direct Marketing - Outbound Telemarketing')],
                ['value' => '5968', 'label' => __('Direct Marketing - Subscription')],
                ['value' => '5962', 'label' => __('Direct Marketing - Travel')],
                ['value' => '5310', 'label' => __('Discount Stores')],
                ['value' => '8011', 'label' => __('Doctors')],
                ['value' => '5963', 'label' => __('Door-To-Door Sales')],
                ['value' => '5714', 'label' => __('Drapery, Window Covering, and Upholstery Stores')],
                ['value' => '5813', 'label' => __('Drinking Places')],
                ['value' => '5912', 'label' => __('Drug Stores and Pharmacies')],
                ['value' => '5122', 'label' => __('Drugs, Drug Proprietaries, and Druggist Sundries')],
                ['value' => '7216', 'label' => __('Dry Cleaners')],
                ['value' => '5099', 'label' => __('Durable Goods (Not Elsewhere Classified)')],
                ['value' => '5309', 'label' => __('Duty Free Stores')],
                ['value' => '5812', 'label' => __('Eating Places, Restaurants')],
                ['value' => '8299', 'label' => __('Educational Services')],
                ['value' => '5997', 'label' => __('Electric Razor Stores')],
                ['value' => '5065', 'label' => __('Electrical Parts and Equipment')],
                ['value' => '1731', 'label' => __('Electrical Services')],
                ['value' => '7622', 'label' => __('Electronics Repair Shops')],
                ['value' => '5732', 'label' => __('Electronics Stores')],
                ['value' => '8211', 'label' => __('Elementary, Secondary Schools')],
                ['value' => '7361', 'label' => __('Employment/Temp Agencies')],
                ['value' => '7394', 'label' => __('Equipment Rental')],
                ['value' => '7342', 'label' => __('Exterminating Services')],
                ['value' => '5651', 'label' => __('Family Clothing Stores')],
                ['value' => '5814', 'label' => __('Fast Food Restaurants')],
                ['value' => '6012', 'label' => __('Financial Institutions')],
                ['value' => '9222', 'label' => __('Fines - Government Administrative Entities')],
                ['value' => '5718', 'label' => __('Fireplace, Fireplace Screens, and Accessories Stores')],
                ['value' => '5713', 'label' => __('Floor Covering Stores')],
                ['value' => '5992', 'label' => __('Florists')],
                ['value' => '5193', 'label' => __('Florists Supplies, Nursery Stock, and Flowers')],
                ['value' => '5422', 'label' => __('Freezer and Locker Meat Provisioners')],
                ['value' => '5983', 'label' => __('Fuel Dealers (Non Automotive)')],
                ['value' => '7261', 'label' => __('Funeral Services, Crematories')],
                ['value' => '7641', 'label' => __('Furniture Repair, Refinishing')],
                ['value' => '5712', 'label' => __(
                    'Furniture, Home Furnishings, and Equipment Stores, Except Appliances'
                )
                ],
                ['value' => '5681', 'label' => __('Furriers and Fur Shops')],
                ['value' => '1520', 'label' => __('General Services')],
                ['value' => '5947', 'label' => __('Gift, Card, Novelty, and Souvenir Shops')],
                ['value' => '5231', 'label' => __('Glass, Paint, and Wallpaper Stores')],
                ['value' => '5950', 'label' => __('Glassware, Crystal Stores')],
                ['value' => '7992', 'label' => __('Golf Courses - Public')],
                ['value' => '9399', 'label' => __('Government Services (Not Elsewhere Classified)')],
                ['value' => '5411', 'label' => __('Grocery Stores, Supermarkets')],
                ['value' => '5251', 'label' => __('Hardware Stores')],
                ['value' => '5072', 'label' => __('Hardware, Equipment, and Supplies')],
                ['value' => '7298', 'label' => __('Health and Beauty Spas')],
                ['value' => '5975', 'label' => __('Hearing Aids Sales and Supplies')],
                ['value' => '1711', 'label' => __('Heating, Plumbing, A/C')],
                ['value' => '5945', 'label' => __('Hobby, Toy, and Game Shops')],
                ['value' => '5200', 'label' => __('Home Supply Warehouse Stores')],
                ['value' => '8062', 'label' => __('Hospitals')],
                ['value' => '7011', 'label' => __('Hotels, Motels, and Resorts')],
                ['value' => '5722', 'label' => __('Household Appliance Stores')],
                ['value' => '5085', 'label' => __('Industrial Supplies (Not Elsewhere Classified)')],
                ['value' => '7375', 'label' => __('Information Retrieval Services')],
                ['value' => '6399', 'label' => __('Insurance - Default')],
                ['value' => '6300', 'label' => __('Insurance Underwriting, Premiums')],
                ['value' => '9950', 'label' => __('Intra-Company Purchases')],
                ['value' => '5944', 'label' => __('Jewelry Stores, Watches, Clocks, and Silverware Stores')],
                ['value' => '0780', 'label' => __('Landscaping Services')],
                ['value' => '7211', 'label' => __('Laundries')],
                ['value' => '7210', 'label' => __('Laundry, Cleaning Services')],
                ['value' => '8111', 'label' => __('Legal Services, Attorneys')],
                ['value' => '5948', 'label' => __('Luggage and Leather Goods Stores')],
                ['value' => '5211', 'label' => __('Lumber, Building Materials Stores')],
                ['value' => '6010', 'label' => __('Manual Cash Disburse')],
                ['value' => '4468', 'label' => __('Marinas, Service and Supplies')],
                ['value' => '1740', 'label' => __('Masonry, Stonework, and Plaster')],
                ['value' => '7297', 'label' => __('Massage Parlors')],
                ['value' => '8099', 'label' => __('Medical Services')],
                ['value' => '8071', 'label' => __('Medical and Dental Labs')],
                ['value' => '5047', 'label' => __('Medical, Dental, Ophthalmic, and Hospital Equipment and Supplies')],
                ['value' => '8699', 'label' => __('Membership Organizations')],
                ['value' => '5611', 'label' => __('Mens and Boys Clothing and Accessories Stores')],
                ['value' => '5691', 'label' => __('Mens, Womens Clothing Stores')],
                ['value' => '5051', 'label' => __('Metal Service Centers')],
                ['value' => '5699', 'label' => __('Miscellaneous Apparel and Accessory Shops')],
                ['value' => '5599', 'label' => __('Miscellaneous Auto Dealers')],
                ['value' => '7399', 'label' => __('Miscellaneous Business Services')],
                ['value' => '5499', 'label' => __(
                    'Miscellaneous Food Stores - Convenience Stores and Specialty Markets'
                )
                ],
                ['value' => '5399', 'label' => __('Miscellaneous General Merchandise')],
                ['value' => '7299', 'label' => __('Miscellaneous General Services')],
                ['value' => '5719', 'label' => __('Miscellaneous Home Furnishing Specialty Stores')],
                ['value' => '2741', 'label' => __('Miscellaneous Publishing and Printing')],
                ['value' => '7999', 'label' => __('Miscellaneous Recreation Services')],
                ['value' => '7699', 'label' => __('Miscellaneous Repair Shops')],
                ['value' => '5999', 'label' => __('Miscellaneous Specialty Retail')],
                ['value' => '5271', 'label' => __('Mobile Home Dealers')],
                ['value' => '7832', 'label' => __('Motion Picture Theaters')],
                ['value' => '4214', 'label' => __('Motor Freight Carriers and Trucking')],
                ['value' => '5592', 'label' => __('Motor Homes Dealers')],
                ['value' => '5013', 'label' => __('Motor Vehicle Supplies and New Parts')],
                ['value' => '5571', 'label' => __('Motorcycle Shops and Dealers')],
                ['value' => '5561', 'label' => __('Motorcycle Shops, Dealers')],
                ['value' => '5733', 'label' => __('Music Stores-Musical Instruments, Pianos, and Sheet Music')],
                ['value' => '5994', 'label' => __('News Dealers and Newsstands')],
                ['value' => '6051', 'label' => __('Non-FI, Money Orders')],
                ['value' => '6540', 'label' => __('Non-FI, Stored Value Card Purchase/Load')],
                ['value' => '5199', 'label' => __('Nondurable Goods (Not Elsewhere Classified)')],
                ['value' => '5261', 'label' => __('Nurseries, Lawn and Garden Supply Stores')],
                ['value' => '8050', 'label' => __('Nursing/Personal Care')],
                ['value' => '5021', 'label' => __('Office and Commercial Furniture')],
                ['value' => '8043', 'label' => __('Opticians, Eyeglasses')],
                ['value' => '8042', 'label' => __('Optometrists, Ophthalmologist')],
                ['value' => '5976', 'label' => __('Orthopedic Goods - Prosthetic Devices')],
                ['value' => '8031', 'label' => __('Osteopaths')],
                ['value' => '5921', 'label' => __('Package Stores-Beer, Wine, and Liquor')],
                ['value' => '5198', 'label' => __('Paints, Varnishes, and Supplies')],
                ['value' => '7523', 'label' => __('Parking Lots, Garages')],
                ['value' => '4112', 'label' => __('Passenger Railways')],
                ['value' => '5933', 'label' => __('Pawn Shops')],
                ['value' => '5995', 'label' => __('Pet Shops, Pet Food, and Supplies')],
                ['value' => '5172', 'label' => __('Petroleum and Petroleum Products')],
                ['value' => '7395', 'label' => __('Photo Developing')],
                ['value' => '7221', 'label' => __('Photographic Studios')],
                ['value' => '5044', 'label' => __('Photographic, Photocopy, Microfilm Equipment, and Supplies')],
                ['value' => '7829', 'label' => __('Picture/Video Production')],
                ['value' => '5131', 'label' => __('Piece Goods, Notions, and Other Dry Goods')],
                ['value' => '5074', 'label' => __('Plumbing, Heating Equipment, and Supplies')],
                ['value' => '8651', 'label' => __('Political Organizations')],
                ['value' => '9402', 'label' => __('Postal Services - Government Only')],
                ['value' => '5094', 'label' => __('Precious Stones and Metals, Watches and Jewelry')],
                ['value' => '8999', 'label' => __('Professional Services')],
                ['value' => '4225', 'label' => __('Public Warehousing and Storage')],
                ['value' => '7338', 'label' => __('Quick Copy, Repro, and Blueprint')],
                ['value' => '4011', 'label' => __('Railroads')],
                ['value' => '6513', 'label' => __('Real Estate Agents and Managers - Rentals')],
                ['value' => '5735', 'label' => __('Record Stores')],
                ['value' => '7519', 'label' => __('Recreational Vehicle Rentals')],
                ['value' => '5973', 'label' => __('Religious Goods Stores')],
                ['value' => '8661', 'label' => __('Religious Organizations')],
                ['value' => '1761', 'label' => __('Roofing/Siding, Sheet Metal')],
                ['value' => '7339', 'label' => __('Secretarial Support Services')],
                ['value' => '6211', 'label' => __('Security Brokers/Dealers')],
                ['value' => '5541', 'label' => __('Service Stations')],
                ['value' => '5949', 'label' => __('Sewing, Needlework, Fabric, and Piece Goods Stores')],
                ['value' => '7251', 'label' => __('Shoe Repair/Hat Cleaning')],
                ['value' => '5661', 'label' => __('Shoe Stores')],
                ['value' => '7629', 'label' => __('Small Appliance Repair')],
                ['value' => '5598', 'label' => __('Snowmobile Dealers')],
                ['value' => '1799', 'label' => __('Special Trade Services')],
                ['value' => '2842', 'label' => __('Specialty Cleaning')],
                ['value' => '5941', 'label' => __('Sporting Goods Stores')],
                ['value' => '7032', 'label' => __('Sporting/Recreation Camps')],
                ['value' => '7941', 'label' => __('Sports Clubs/Fields')],
                ['value' => '5655', 'label' => __('Sports and Riding Apparel Stores')],
                ['value' => '5972', 'label' => __('Stamp and Coin Stores')],
                ['value' => '5111', 'label' => __('Stationary, Office Supplies, Printing and Writing Paper')],
                ['value' => '5943', 'label' => __('Stationery Stores, Office, and School Supply Stores')],
                ['value' => '5996', 'label' => __('Swimming Pools Sales')],
                ['value' => '4723', 'label' => __('TUI Travel - Germany')],
                ['value' => '5697', 'label' => __('Tailors, Alterations')],
                ['value' => '9311', 'label' => __('Tax Payments - Government Agencies')],
                ['value' => '7276', 'label' => __('Tax Preparation Services')],
                ['value' => '4121', 'label' => __('Taxicabs/Limousines')],
                ['value' => '4812', 'label' => __('Telecommunication Equipment and Telephone Sales')],
                ['value' => '4814', 'label' => __('Telecommunication Services')],
                ['value' => '4821', 'label' => __('Telegraph Services')],
                ['value' => '5998', 'label' => __('Tent and Awning Shops')],
                ['value' => '8734', 'label' => __('Testing Laboratories')],
                ['value' => '7922', 'label' => __('Theatrical Ticket Agencies')],
                ['value' => '7012', 'label' => __('Timeshares')],
                ['value' => '7534', 'label' => __('Tire Retreading and Repair')],
                ['value' => '4784', 'label' => __('Tolls/Bridge Fees')],
                ['value' => '7991', 'label' => __('Tourist Attractions and Exhibits')],
                ['value' => '7549', 'label' => __('Towing Services')],
                ['value' => '7033', 'label' => __('Trailer Parks, Campgrounds')],
                ['value' => '4789', 'label' => __('Transportation Services (Not Elsewhere Classified)')]
            ];
        }
        return $this->_options;
    }
}
