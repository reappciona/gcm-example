package kr.appkr.gcm_example;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.SocketException;
import java.net.SocketTimeoutException;
import java.net.URL;
import java.net.URLDecoder;
import java.net.UnknownHostException;

import org.json.JSONException;
import org.json.JSONObject;

import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager.NameNotFoundException;
import android.graphics.Color;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.text.ClipboardManager;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.View.OnLongClickListener;
import android.view.inputmethod.InputMethodManager;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemSelectedListener;
import android.widget.ArrayAdapter;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import com.google.android.gms.gcm.GoogleCloudMessaging;

/**
 * Main UI for the demo app.
 * email : pyo@airplug.com
 * by droidTeam
 */

//"deprecation" annotation is care for ClipboardManager method
@SuppressWarnings("deprecation")
public class MainActivity extends Activity implements OnClickListener{

	private Context context;
	private final String DIRECT_INPUT = "Direct input";
	
	/**
	 * Substitute you own sender ID here. This is the project number you got
	 * from the API Console, as described in "Getting Started."
	 */
	private final String SENDER_ID = "Your sender ID";
	
	private final String PROPERTY_REG_ID = "registration_id";
	private final String PROPERTY_APP_VERSION = "appVersion";
	private GoogleCloudMessaging gcm;
	private TextView gcmStatusTV;
	private TextView gcmIDTV;
	private String regid;
	
	/**
	 * Substitute you own 3rd-party App Server URLs here.
	 */
	private final String[] urls = {"http://promote.airplug.com/gcm-example/api/", "https://promote.airplug.com/gcm-example/api/", DIRECT_INPUT};
	
	private InputMethodManager imm;
	private EditText requestET;
	private TextView resultCodeTV;
	private TextView resultMsgTV;
	private Spinner urlSpinner;
	private String query;
	private String url;
	private DeviceInfo info;
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
		context = this;

		info = new DeviceInfo(context);
		imm = (InputMethodManager)getSystemService(Context.INPUT_METHOD_SERVICE);
		findViewById(R.id.gcmRegiBtn).setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				gcmRegi();
			}
		});
		findViewById(R.id.regiBtn).setOnClickListener(this);
		findViewById(R.id.unRegiBtn).setOnClickListener(this);
		findViewById(R.id.snedMsgBtn).setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				//startActivity --> send gcm message(web browser)
				Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("http://promote.airplug.com/gcm-example/?only=1&uuid="+info.getDeviceAndroidID()));
				startActivity(intent);
			}
		});
		requestET = (EditText)findViewById(R.id.requestET);
		urlSpinner = (Spinner)findViewById(R.id.urlSpinner);
		urlSpinner.setOnItemSelectedListener(itemSelectedListener);
		ArrayAdapter<String> urlAdapter = new ArrayAdapter<String>(context, android.R.layout.simple_spinner_item, urls);
		urlAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
		urlSpinner.setAdapter(urlAdapter);

		gcmIDTV = (TextView)findViewById(R.id.gcmIDTV);
		if(Build.VERSION.SDK_INT < Build.VERSION_CODES.HONEYCOMB){
			gcmIDTV.setOnLongClickListener(new OnLongClickListener() {
				@Override
				public boolean onLongClick(View v) {
					gcmIDTV.setTextColor(Color.parseColor("#00D8FF"));
					AlertDialog.Builder dialog = new AlertDialog.Builder(context);
					dialog.setPositiveButton("copy", new DialogInterface.OnClickListener() {
						@Override
						public void onClick(DialogInterface dialog, int which) {
							ClipboardManager clipboard = (ClipboardManager) getSystemService(Context.CLIPBOARD_SERVICE);
							clipboard.setText(gcmIDTV.getText());
							Toast.makeText(context, "Copied gcm id", Toast.LENGTH_SHORT).show();
							gcmIDTV.setTextColor(Color.parseColor("#363636"));
						}
					});
					dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
						@Override
						public void onCancel(DialogInterface dialog) {
							gcmIDTV.setTextColor(Color.parseColor("#363636"));
						}
					});
					dialog.show();
					return false;
				}
			});
		}else{
			setTextIsSelectable();
		}
		gcmStatusTV = (TextView)findViewById(R.id.gcmStatusTV);
		resultCodeTV = (TextView)findViewById(R.id.resultCodeTV);
		resultMsgTV = (TextView)findViewById(R.id.resultMsgTV);

	}
 
	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	private void setTextIsSelectable(){
		gcmIDTV.setTextIsSelectable(true); 
	}

	@Override
	protected void onStart() {
		super.onStart();
		regid = getRegistrationId(context);
		if(!regid.isEmpty()){
			gcmRegi();
		}
	}

	private OnItemSelectedListener itemSelectedListener = new OnItemSelectedListener() {
		@Override
		public void onItemSelected(AdapterView<?> arg0, View arg1, int arg2,
				long arg3) {
			
			String value = urlSpinner.getSelectedItem().toString();
			if(!value.equals(DIRECT_INPUT)){
				requestET.setVisibility(View.GONE);
				imm.hideSoftInputFromWindow(requestET.getWindowToken(), 0);
			}else{
				requestET.setVisibility(View.VISIBLE);
				requestET.setText("");
				new Handler().postDelayed(new Runnable() {
					@Override
					public void run() {
						imm.showSoftInput(requestET, 0);
					}
				}, 0);
				requestET.requestFocus();
			}
		}
		
		@Override
		public void onNothingSelected(AdapterView<?> arg0) {}
	};

	@Override
	public void onClick(View v) {
		switch (v.getId()) {
		case R.id.regiBtn:
			query = "register";
			break;
		case R.id.unRegiBtn:
			query = "unregister";
			break;
		}

		String value = urlSpinner.getSelectedItem().toString();
		if(!value.equals(DIRECT_INPUT)){
			url = value;
		}else{
			url = requestET.getText().toString();
		}

		AirPlugGcmRequest airPlugGcmRequest = new AirPlugGcmRequest();
		airPlugGcmRequest.execute(url);
	}

	private void gcmRegi(){
		gcm = GoogleCloudMessaging.getInstance(context);
		regid = getRegistrationId(context);
		if (regid.isEmpty()) {
			registerInBackground();
		}else{
			gcmStatusTV.setText("Registered");
			gcmIDTV.setText(regid);
		}
	}

	/**
	 * Stores the registration ID and app versionCode in the application's
	 * {@code SharedPreferences}.
	 *
	 * @param context application's context.
	 * @param regId registration ID
	 */
	private void storeRegistrationId(Context context, String regId) {
		final SharedPreferences prefs = getGcmPreferences(context);
		int appVersion = getAppVersion(context);
		SharedPreferences.Editor editor = prefs.edit();
		editor.putString(PROPERTY_REG_ID, regId);
		editor.putInt(PROPERTY_APP_VERSION, appVersion);
		editor.commit();
	}

	/**
	 * Gets the current registration ID for application on GCM service.
	 * <p>
	 * If result is empty, the app needs to register.
	 *
	 * @return registration ID, or empty string if there is no existing
	 *         registration ID.
	 */
	private String getRegistrationId(Context context) {
		final SharedPreferences prefs = getGcmPreferences(context);
		String registrationId = prefs.getString(PROPERTY_REG_ID, "");
		if (registrationId.isEmpty()) {
			return "";
		}
		
		// Check if app was updated; if so, it must clear the registration ID
	    // since the existing regID is not guaranteed to work with the new
	    // app version.
		int registeredVersion = prefs.getInt(PROPERTY_APP_VERSION, Integer.MIN_VALUE);
		int currentVersion = getAppVersion(context);
		if (registeredVersion != currentVersion) {
			return "";
		}
		return registrationId;
	}

	/**
	 * Registers the application with GCM servers asynchronously.
	 * <p>
	 * Stores the registration ID and app versionCode in the application's
	 * shared preferences.
	 */
	private void registerInBackground() {
		new AsyncTask<Void, Void, String>() {
			@Override
			protected String doInBackground(Void... params) {
				String msg = "";
				try {
					if (gcm == null) {
						gcm = GoogleCloudMessaging.getInstance(context);
					}
					regid = gcm.register(SENDER_ID);
					
					// You should send the registration ID to your server over HTTP,
	                // so it can use GCM/HTTP or CCS to send messages to your app.
	                // The request to your server should be authenticated if your app
	                // is using accounts.
					//sendRegistrationIdToBackend();
					
					runOnUiThread(new Runnable() {
						public void run() {
							gcmIDTV.setText(regid);
						}
					});

					// For this demo: we don't need to send it because the device
	                // will send upstream messages to a server that echo back the
	                // message using the 'from' address in the message.

	                // Persist the regID - no need to register again.
					storeRegistrationId(context, regid);
					
					msg = "200";
				} catch (IOException ex) {
					
					// If there is an error, don't just keep trying to register.
	                // Require the user to click a button again, or perform
	                // exponential back-off.
					msg = "300";
				}
				return msg;
			}

			@Override
			protected void onPostExecute(String msg) {
				if(msg.equals("200")){
					gcmStatusTV.setText("Newly registered");
				}else{
					gcmStatusTV.setText("GCM failure to register");
				}
			}
		}.execute(null, null, null);
	}

	/**
	 * @return Application's version code from the {@code PackageManager}.
	 */
	private int getAppVersion(Context context) {
		try {
			PackageInfo packageInfo = context.getPackageManager()
					.getPackageInfo(context.getPackageName(), 0);
			return packageInfo.versionCode;
		} catch (NameNotFoundException e) {
			// should never happen
			throw new RuntimeException("Could not get package name: " + e);
		}
	}

	/**
	 * @return Application's {@code SharedPreferences}.
	 */
	private SharedPreferences getGcmPreferences(Context context) {
		// This sample app persists the registration ID in shared preferences, but
	    // how you store the regID in your app is up to you.
		return getSharedPreferences(MainActivity.class.getSimpleName(),
				Context.MODE_PRIVATE);
	}

	private String userAgent(){
		String USER_AGENT = "";
		USER_AGENT = 
				info.getPackageName() + "; "
						+ info.getPackageVersionName() + "/" + info.getPackageVersionCode() + "; "
						+ info.getDeviceCountry() + "/" + info.getDeviceLanguage() + "; "
						+ info.getDeviceBrand() + "; "
						+ info.getDeviceModel() + "; "
						+ info.getDeviceVersionName() + "/" + info.getDeviceOsVersion() + "; ";
		return USER_AGENT;
	}
	
	
	/**
	 * Sends the registration ID to your server over HTTP, so it can use GCM/HTTP
	 * or CCS to send messages to your app. Not needed for this demo since the
	 * device sends upstream messages to a server that echoes back the message
	 * using the 'from' address in the message.
	 */
	
//	private void sendRegistrationIdToBackend() {
//	    // Your implementation here.
//		new AirPlugGcmRequest().execute(regid);
//	}
	
	/**
	 * 3rd-party App Server
	 */
	private class AirPlugGcmRequest extends AsyncTask<String, Void, RequestResult> {

		RequestResult result = new RequestResult();
		HttpURLConnection conn = null;
		BufferedReader br = null; 
		JSONObject jsonObj = null;
		StringBuffer sb = null;
		String line = null;

		@Override
		protected RequestResult doInBackground(String... params) {

			try{
				URL url = null;
				url = new URL(params[0]);
				conn = (HttpURLConnection)url.openConnection();	
				conn.setRequestMethod("POST");
				conn.setRequestProperty("User-Agent", userAgent());
				conn.setDoOutput(true);
				conn.setDoInput(true);
				conn.setUseCaches(false);

				sb = new StringBuffer();
				sb.append("query="+query+"&")
				.append("package="+info.getPackageName()+"&")
				.append("uuid="+info.getDeviceAndroidID()+"&")
				.append("gcmID="+regid+"&")
				.append("l=en&")
				.append("v=1.0");
				PrintWriter pw = new PrintWriter(new OutputStreamWriter(conn.getOutputStream(), "UTF-8"));
				pw.write(sb.toString());
				pw.flush();

				conn.setConnectTimeout(10 * 1000);
				conn.setReadTimeout(10 * 1000);
				conn.connect();

				br = new BufferedReader(new InputStreamReader(conn.getInputStream(),"UTF-8"));
				line = decodingString(br.readLine());
				jsonObj = new JSONObject(line);
				result.code = jsonObj.getInt("code");
				result.msg = jsonObj.getString("message");
				return result;

			} catch (SocketException e) {
				result.code = 900;
				result.msg = "SocketException";
				return result;
			} catch (UnknownHostException e) {
				result.msg = "UnknownHostException";
				result.code = 900;
				return result;
			} catch (SocketTimeoutException e) {
				result.msg = "SocketTimeoutException";
				result.code = 900;
				return result;
			} catch (FileNotFoundException e) {
				result.msg = "FileNotFoundException";
				result.code = 900;
				return result;
			} catch (IOException e) {
				result.msg = "IOException";
				result.code = 900;
				return result;
			} catch (JSONException e) {
				result.msg = "JSONException";
				result.code = 900;
				return result;
			}
		}

		@Override
		protected void onPostExecute(RequestResult result) {
			resultCodeTV.setText(String.valueOf(result.code));
			resultMsgTV.setText(result.msg);
		}
	}

	private class RequestResult {
		public int code;
		public String msg;
	}

	private String decodingString(String url){
		try {
			return URLDecoder.decode(url, "UTF-8");
		} catch (UnsupportedEncodingException e) {
		}
		return null;
	}
}