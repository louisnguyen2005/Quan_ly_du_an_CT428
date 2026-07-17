param(
  [string]$BaseUrl = "http://localhost/WebPrograming/Quan_ly_du_an_CT428/index.php",
  [string]$Email = "manager@gmail.com",
  [string]$Password = "ChangeMe@123"
)

$login = Invoke-RestMethod -Method Post -Uri "$BaseUrl?action=apiLogin" -Body @{email=$Email; password=$Password}
$access = $login.jwt.access_token
$refresh = $login.jwt.refresh_token
Write-Host "API Login: PASS"

$me = Invoke-RestMethod -Method Get -Uri "$BaseUrl?action=apiMe" -Headers @{Authorization="Bearer $access"}
Write-Host "API Me: PASS - user" $me.user.username

$renew = Invoke-RestMethod -Method Post -Uri "$BaseUrl?action=apiRefreshToken" -Body @{refresh_token=$refresh}
Write-Host "Refresh Token: PASS"

Invoke-RestMethod -Method Post -Uri "$BaseUrl?action=apiLogout" -Headers @{Authorization="Bearer $($renew.jwt.access_token)"} | Out-Null
Write-Host "API Logout: PASS"
