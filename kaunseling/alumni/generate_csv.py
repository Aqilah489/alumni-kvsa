import csv

# Data dummy yang realistik untuk Malaysia
data = [
    {
        "No Matriks": "A241001",
        "Nama": "Muhammad Adam Bin Abdullah",
        "Emel": "adam.abdullah24@alumni.kvsa.edu",
        "Batch Tahun": 2024,
        "Program": "Diploma Teknologi Komputer",
        "No Telefon": "012-3456789",
        "Alamat": "No 15, Jalan SS2/24, Seksyen 2, 47300 Petaling Jaya, Selangor"
    },
    {
        "No Matriks": "A231002",
        "Nama": "Nur Aisyah Binti Mohd Rizal",
        "Emel": "aisyah.rizal23@alumni.kvsa.edu",
        "Batch Tahun": 2023,
        "Program": "Diploma Teknologi Maklumat",
        "No Telefon": "013-9876543",
        "Alamat": "No 8, Lorong Bunga Raya 3, Taman Sri Ampang, 68000 Ampang, Selangor"
    },
    {
        "No Matriks": "A221003",
        "Nama": "Siti Zulaikha Binti Ismail",
        "Emel": "zulaikha.ismail22@alumni.kvsa.edu",
        "Batch Tahun": 2022,
        "Program": "Diploma Teknologi Perisian",
        "No Telefon": "016-4567890",
        "Alamat": "No 42, Jalan Mutiara 1, Taman Mutiara, 14000 Bukit Mertajam, Pulau Pinang"
    },
    {
        "No Matriks": "A241004",
        "Nama": "Tan Wei Jie",
        "Emel": "weijie.tan24@alumni.kvsa.edu",
        "Batch Tahun": 2024,
        "Program": "Diploma Teknologi Rangkaian",
        "No Telefon": "017-2345678",
        "Alamat": "No 5, Jalan Harmoni 2/3, Taman Desa Harmoni, 81100 Johor Bahru, Johor"
    },
    {
        "No Matriks": "A231005",
        "Nama": "Kavitha A/P Rajendran",
        "Emel": "kavitha.rajendran23@alumni.kvsa.edu",
        "Batch Tahun": 2023,
        "Program": "Diploma Teknologi Multimedia",
        "No Telefon": "019-8765432",
        "Alamat": "No 10, Persiaran Greentown, Greentown Business Centre, 30450 Ipoh, Perak"
    }
]

# Simpan ke file CSV
csv_file = "alumni_dummy_5_data.csv"
with open(csv_file, 'w', newline='', encoding='utf-8') as csvfile:
    fieldnames = ["No Matriks", "Nama", "Emel", "Batch Tahun", "Program", "No Telefon", "Alamat"]
    writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
    
    writer.writeheader()
    writer.writerows(data)

# Paparkan data
print("=== 5 Data Alumni Malaysia (Realistik) ===\n")
for i, row in enumerate(data, 1):
    print(f"Data ke-{i}:")
    print(f"  No Matriks   : {row['No Matriks']}")
    print(f"  Nama         : {row['Nama']}")
    print(f"  Emel         : {row['Emel']}")
    print(f"  Batch Tahun  : {row['Batch Tahun']}")
    print(f"  Program      : {row['Program']}")
    print(f"  No Telefon   : {row['No Telefon']}")
    print(f"  Alamat       : {row['Alamat']}")
    print()

print(f"✅ File '{csv_file}' telah berjaya dicipta dengan {len(data)} data.")

# Tunjukkan isi CSV
print("\n=== Preview File CSV ===")
with open(csv_file, 'r', encoding='utf-8') as f:
    for line in f:
        print(line.strip())