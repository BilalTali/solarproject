const axios = require('axios');
async function test() {
    try {
        const token = require('child_process').execSync(`cd ../backend && php artisan tinker --execute="echo App\\\\Models\\\\User::where('role', 'admin')->first()->createToken('test')->plainTextToken;"`).toString().trim();
        const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/leads', {
            headers: { Authorization: `Bearer ${token}` }
        });
        console.log("SUCCESS:", JSON.stringify(res.data, null, 2).substring(0, 500));
    } catch (err) {
        console.error("ERROR:", err.response ? err.response.data : err.message);
    }
}
test();
