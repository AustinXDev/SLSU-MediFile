import { initPassword } from "./password.js";
import { validateInput } from "./validate.js";
import api from "../../../../api/api.js";

window.addEventListener("DOMContentLoaded", () => {
  initPassword();

  const form = document.getElementById("loginForm");
  const userField = document.getElementById("userField");
  const passField = document.getElementById("passField");
  const passInput = document.getElementById("password");
  const loginBtn = document.getElementById("login-btn");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("username").value.trim();

    const password = document.getElementById("password").value;

    const validate = validateInput({
      username,
      password,
    });

    if (!validate) {
      return;
    }

    loginBtn.disabled = true;
    loginBtn.textContent = "Sign In...";

    try {
      /**
       * LOGIN REQUEST
       */
      const loginResponse = await api.post("auth/login_process.php", {
        username,
        password,
      });

      const loginData = loginResponse.data;

      console.log("LOGIN RESPONSE:", loginData);

      /**
       * TWO FACTOR REQUIRED
       */
      if (loginData.status === "2fa_required") {
        loginBtn.disabled = false;
        loginBtn.textContent = "Sign In";

        let verifyingLoginCode = false;
        let loginResult = null;

        StatusModal.verify(
          "Verify It's You",
          "Enter the 6-digit code we sent to your registered email.",
          {
            /**
             * OTP SUBMIT
             */
            onSubmit: async (code) => {
              if (verifyingLoginCode) {
                return false;
              }

              verifyingLoginCode = true;

              try {
                const verifyResponse = await api.post("auth/verify.php", {
                  admin_id: loginData.admin_id,
                  purpose: "login",
                  code,
                });

                loginResult = verifyResponse.data;

                console.log("OTP RESPONSE:", loginResult);

                return loginResult.status === "success";
              } catch (error) {
                const verifyData = error.response?.data ?? {
                  message: "Unable to verify OTP.",
                };

                console.error("OTP ERROR:", verifyData);

                StatusModal.show(
                  "Verification Failed",
                  verifyData.message,
                  "error",
                );

                return false;
              } finally {
                verifyingLoginCode = false;
              }
            },

            /**
             * OTP SUCCESS
             */
            onSuccess: () => {
              StatusModal.show("Welcome Back", loginResult.message, "success");

              loginBtn.disabled = true;
              loginBtn.textContent = "Redirecting...";

              setTimeout(() => {
                if (!loginResult?.redirect) {
                  console.error("Missing redirect:", loginResult);

                  StatusModal.show(
                    "Error",
                    "Login succeeded, but no redirect was provided.",
                    "error",
                  );

                  loginBtn.disabled = false;
                  loginBtn.textContent = "Sign In";

                  return;
                }

                window.location.href = `${loginResult.redirect}`;
              }, 1500);

              form.reset();
            },
          },
        );

        return;
      }

      if (loginData.status === "success") {
        StatusModal.show("Welcome Back", loginData.message, "success");
      }
    } catch (error) {
      const errorData = error.response?.data ?? {
        message: "Unable to process login request.",
      };

      console.error("LOGIN ERROR:", errorData);

      StatusModal.show("Login Failed", errorData.message, "error");
    } finally {
      loginBtn.disabled = false;
      loginBtn.textContent = "Sign In";
    }
  });

  /**
   * Remove validation errors
   */
  document.getElementById("username").addEventListener("input", () => {
    userField.classList.remove("has-error");
  });

  passInput.addEventListener("input", () => {
    passField.classList.remove("has-error");
  });
});
