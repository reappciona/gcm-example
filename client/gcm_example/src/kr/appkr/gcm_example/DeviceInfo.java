package kr.appkr.gcm_example;

import java.util.Locale;

import android.content.Context;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.os.Build;
import android.provider.Settings.Secure;

/**
 * DeviceInfo class for 3rd-party App Server
 */
public class DeviceInfo {

	private String packageName;
	private String packageVersionName;
	private int packageVersionCode;
	private String deviceBrand;
	private String deviceModel;
	private String deviceVersionName;
	private int deviceOsVersion;
	private String deviceCountry;
	private String deviceLanguage;
	private String deviceAndroidID;

	public DeviceInfo(Context context){
		try {
			PackageManager pm = context.getPackageManager();
			PackageInfo p = pm.getPackageInfo(context.getPackageName(), 0);
			packageName = p.packageName;
			deviceAndroidID = Secure.getString(context.getContentResolver(), Secure.ANDROID_ID);
			packageVersionName = p.versionName;
			packageVersionCode = p.versionCode;
			deviceBrand = Build.BRAND;
			deviceModel = Build.MODEL;
			deviceOsVersion = Build.VERSION.SDK_INT;
			deviceVersionName = Build.VERSION.RELEASE;
			
			Locale locale = context.getResources().getConfiguration().locale;
			deviceCountry = locale.getCountry();
			deviceLanguage = locale.getLanguage();

		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public String getPackageName() {
		return packageName;
	}

	public void setPackageName(String packageName) {
		this.packageName = packageName;
	}

	public String getPackageVersionName() {
		return packageVersionName;
	}

	public void setPackageVersionName(String packageVersionName) {
		this.packageVersionName = packageVersionName;
	}

	public int getPackageVersionCode() {
		return packageVersionCode;
	}

	public void setPackageVersionCode(int packageVersionCode) {
		this.packageVersionCode = packageVersionCode;
	}

	public String getDeviceBrand() {
		return deviceBrand;
	}

	public void setDeviceBrand(String deviceBrand) {
		this.deviceBrand = deviceBrand;
	}

	public String getDeviceModel() {
		return deviceModel;
	}

	public void setDeviceModel(String deviceModel) {
		this.deviceModel = deviceModel;
	}

	public String getDeviceVersionName() {
		return deviceVersionName;
	}

	public void setDeviceVersionName(String deviceVersionName) {
		this.deviceVersionName = deviceVersionName;
	}

	public int getDeviceOsVersion() {
		return deviceOsVersion;
	}

	public void setDeviceOsVersion(int deviceOsVersion) {
		this.deviceOsVersion = deviceOsVersion;
	}

	public String getDeviceCountry() {
		return deviceCountry;
	}

	public void setDeviceCountry(String deviceCountry) {
		this.deviceCountry = deviceCountry;
	}

	public String getDeviceLanguage() {
		return deviceLanguage;
	}

	public void setDeviceLanguage(String deviceLanguage) {
		this.deviceLanguage = deviceLanguage;
	}

	public String getDeviceAndroidID() {
		return deviceAndroidID;
	}

	public void setDeviceAndroidID(String deviceAndroidID) {
		this.deviceAndroidID = deviceAndroidID;
	}


}
